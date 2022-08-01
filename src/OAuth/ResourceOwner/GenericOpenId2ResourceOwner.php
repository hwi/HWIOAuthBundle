<?php

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AbstractResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\OAuthErrorHandler;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class GenericOpenId2ResourceOwner extends AbstractResourceOwner
{
    protected string $loginEntrypoint;

    public function getAccessToken(HttpRequest $request, $redirectUri, array $extraParameters = []): array
    {
        OAuthErrorHandler::handleOAuthError($request);

        $nonce = $this->getRequiredRequestParameter($request, 'openid_response_nonce');
        $this->checkNonce($nonce);

        $params = [
            'openid.assoc_handle'   => $this->getRequiredRequestParameter($request, 'openid_assoc_handle'),
            'openid.signed'         => $this->getRequiredRequestParameter($request, 'openid_signed'),
            'openid.sig'            => $this->getRequiredRequestParameter($request, 'openid_sig'),
            'openid.ns'             => $this->getRequiredRequestParameter($request, 'openid_ns'),
            'openid.op_endpoint'    => $this->getRequiredRequestParameter($request, 'openid_op_endpoint'),
            'openid.claimed_id'     => $this->getRequiredRequestParameter($request, 'openid_claimed_id'),
            'openid.identity'       => $this->getRequiredRequestParameter($request, 'openid_identity'),
            'openid.return_to'      => $redirectUri,
            'openid.response_nonce' => $nonce,
            'openid.mode'           => 'check_authentication',
        ];

        $claimedId = $params['openid.claimed_id'] ?? $params['openid.identity'];
        $this->discover($claimedId);

        $response = $this->doGetTokenRequest($this->loginEntrypoint, $params);
        $content  = $response->getBody()->getContents();

        if (preg_match('/is_valid\s*:\s*true/i', $content)) {
            return ['access_token' => $params['openid.identity']];
        }

        throw new AuthenticationException();
    }

    protected function doGetTokenRequest($url, array $parameters = []): ResponseInterface
    {
        return $this->combinedHttpRequest($url, $parameters);
    }

    protected function doGetUserInformationRequest($url, array $parameters = []): ResponseInterface
    {
        return $this->combinedHttpRequest($url, $parameters);
    }

    public function getUserInformation(array $accessToken, array $extraParameters = []): UserResponseInterface
    {
        $url = $this->normalizeUrl($this->options['infos_url'], array_merge(
            [$this->options['attr_name'] => $this->parseUserIdFromIdentity($accessToken['access_token'])],
            $extraParameters
        ));

        $content  = $this->doGetUserInformationRequest($url);
        $response = $this->getUserResponse();
        $response->setData($content instanceof ResponseInterface ? (string) $content->getBody() : $content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    public function getAuthorizationUrl($redirectUri, array $extraParameters = []): string
    {
        $this->discover($this->options['authorization_url']);

        return $this->makeAuthUrlV2(false, $redirectUri);
    }

    public function isCsrfTokenValid($csrfToken): bool
    {
        return true;
    }

    public function handles(HttpRequest $request): bool
    {
        return true;
    }

    protected function getRequiredRequestParameter(HttpRequest $request, string $key): string
    {
        return $request->query->get($key);
    }

    protected function splitNonce(string $nonce): int
    {
        $result = preg_match('/(\d{4})-(\d\d)-(\d\d)T(\d\d):(\d\d):(\d\d)Z(.*)/', $nonce, $matches);

        if ($result !== 1 || count($matches) !== 8) {
            throw new AuthenticationException('Unexpected nonce format');
        }

        list(, $year, $month, $day, $hour, $min, $sec) = $matches;

        try {
            $timestamp = new \DateTime();
            $timestamp->setTimezone(new \DateTimeZone('UTC'));
            $timestamp->setDate((int) $year, (int) $month, (int) $day);
            $timestamp->setTime((int) $hour, (int) $min, (int) $sec);
        } catch (\Throwable $e) {
            throw new AuthenticationException('Timestamp from nonce is not valid', $e->getCode(), $e);
        }

        return $timestamp->getTimestamp();
    }

    protected function checkNonce(string $nonce): void
    {
        $stamp = $this->splitNonce($nonce);
        $skew  = 60;
        $now   = time();

        if ($stamp <= $now - $skew) {
            throw new AuthenticationException("Timestamp from nonce is earlier then time() - {$skew}s");
        }

        if ($stamp >= $now + $skew) {
            throw new AuthenticationException("Timestamp from nonce is older then time() + {$skew}s");
        }
    }

    protected function discover(string $url): string
    {
        $response    = $this->httpRequest($url);
        $contentType = $response->getHeaderLine('Content-Type');

        if (strpos($contentType, 'application/xrds+xml;charset=utf-8') === false) {
            throw new HttpException(415, 'Unexpected Content-Type');
        }

        $xml                   = new \SimpleXMLElement($response->getBody()->getContents());
        $this->version         = 2;
        $this->loginEntrypoint = $xml->XRD->Service->URI;

        return $this->options['authorization_url'];
    }

    protected function httpRequest($url, $content = null, array $headers = [], $method = null): ResponseInterface
    {
        $headers += ['Content-Type' => 'application/x-www-form-urlencoded'];

        return parent::httpRequest($url, $content, $headers, $method);
    }

    protected function combinedHttpRequest(string $url, array $params = []): ResponseInterface
    {
        return $this->httpRequest($url, http_build_query($params, '', '&'));
    }

    protected function makeAuthUrlV2(bool $immediate, string $redirectUri): string
    {
        $params = [
            'openid.ns'         => 'http://specs.openid.net/auth/2.0',
            'openid.mode'       => $immediate ? 'checkid_immediate' : 'checkid_setup',
            'openid.return_to'  => $redirectUri,
            'openid.realm'      => $redirectUri,
            'openid.ns.sreg'    => 'http://openid.net/extensions/sreg/1.1',
            'openid.identity'   => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        ];

        return $this->normalizeUrl($this->loginEntrypoint, $params);
    }

    protected function parseUserIdFromIdentity(string $identity): string
    {
        return $identity;
    }
}
