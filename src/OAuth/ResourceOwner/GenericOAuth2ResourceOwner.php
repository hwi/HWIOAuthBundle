<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Helper\NonceGenerator;
use HWI\Bundle\OAuthBundle\Security\OAuthErrorHandler;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
abstract class GenericOAuth2ResourceOwner extends AbstractResourceOwner
{
    public const TYPE = null; // it must be null

    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        if ($this->options['use_bearer_authorization']) {
            $content = $this->httpRequest(
                $this->normalizeUrl($this->options['infos_url'], $extraParameters),
                null,
                ['Authorization' => 'Bearer '.$accessToken['access_token']]
            );
        } else {
            $content = $this->doGetUserInformationRequest(
                $this->normalizeUrl(
                    $this->options['infos_url'],
                    array_merge([$this->options['attr_name'] => $accessToken['access_token']], $extraParameters)
                )
            );
        }

        try {
            $response = $this->getUserResponse();
            $response->setData($content->toArray(false));
            $response->setResourceOwner($this);
            $response->setOAuthToken(new OAuthToken($accessToken));

            return $response;
        } catch (TransportExceptionInterface|JsonException $e) {
            throw new HttpTransportException('Error while sending HTTP request', $this->getName(), $e->getCode(), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = [])
    {
        if ($this->options['csrf']) {
            $this->handleCsrfToken();
        }

        $parameters = array_merge([
            'response_type' => 'code',
            'client_id' => $this->options['client_id'],
            'scope' => $this->options['scope'],
            'state' => $this->state->encode(),
            'redirect_uri' => $redirectUri,
        ], $extraParameters);

        return $this->normalizeUrl($this->options['authorization_url'], $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(HttpRequest $request, $redirectUri, array $extraParameters = [])
    {
        OAuthErrorHandler::handleOAuthError($request);

        $parameters = array_merge([
            'code' => $request->query->get('code'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ], $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = [])
    {
        $parameters = array_merge([
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ], $extraParameters);

        $response = $this->doGetTokenRequest($this->options['access_token_url'], $parameters);
        $response = $this->getResponseContent($response);

        $this->validateResponseContent($response);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeToken($token)
    {
        if (!isset($this->options['revoke_token_url'])) {
            throw new AuthenticationException('OAuth error: "Method unsupported."');
        }

        $parameters = [
            'client_id' => $this->options['client_id'],
            'client_secret' => $this->options['client_secret'],
        ];

        $response = $this->httpRequest($this->normalizeUrl($this->options['revoke_token_url'], ['token' => $token]), $parameters, [], 'DELETE');

        return 200 === $response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    public function handles(HttpRequest $request)
    {
        return $request->query->has('code');
    }

    /**
     * {@inheritdoc}
     */
    public function isCsrfTokenValid($csrfToken)
    {
        // Mark token valid when validation is disabled
        if (!$this->options['csrf']) {
            return true;
        }

        if (null === $csrfToken) {
            throw new AuthenticationException('Given CSRF token is not valid.');
        }

        try {
            return null !== $this->storage->fetch($this, urldecode($csrfToken), 'csrf_state');
        } catch (\InvalidArgumentException $e) {
            throw new AuthenticationException('Given CSRF token is not valid.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function shouldRefreshOnExpire()
    {
        return $this->options['refresh_on_expire'] ?? false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetTokenRequest($url, array $parameters = [])
    {
        $headers = [];
        if ($this->options['use_authorization_to_get_token']) {
            if ($this->options['client_secret']) {
                $headers['Authorization'] = 'Basic '.base64_encode($this->options['client_id'].':'.$this->options['client_secret']);
            }
        } else {
            $parameters['client_id'] = $this->options['client_id'];
            $parameters['client_secret'] = $this->options['client_secret'];
        }

        return $this->httpRequest($url, http_build_query($parameters, '', '&'), $headers);
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = [])
    {
        return $this->httpRequest($url, http_build_query($parameters, '', '&'));
    }

    /**
     * @param mixed $response the 'parsed' content based on the response headers
     *
     * @throws AuthenticationException If an OAuth error occurred or no access token is found
     */
    protected function validateResponseContent($response)
    {
        if (isset($response['error_description'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['error_description']));
        }

        if (isset($response['error'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['error']['message'] ?? $response['error']));
        }

        if (!isset($response['access_token'])) {
            throw new AuthenticationException('Not a valid access token.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'attr_name' => 'access_token',
            'use_commas_in_scope' => false,
            'use_bearer_authorization' => true,
            'use_authorization_to_get_token' => true,
            'refresh_on_expire' => false,
        ]);

        $resolver->setDefined('revoke_token_url');
        $resolver->setAllowedValues('refresh_on_expire', [true, false]);

        // Unfortunately some resource owners break the spec by using commas instead
        // of spaces to separate scopes (Disqus, Facebook, Github, Vkontante)
        $scopeNormalizer = function (Options $options, $value) {
            if (!$value) {
                return null;
            }

            if (!$options['use_commas_in_scope']) {
                return $value;
            }

            return str_replace(',', ' ', $value);
        };

        $resolver->setNormalizer('scope', $scopeNormalizer);
    }

    /**
     * {@inheritdoc}
     */
    protected function httpRequest($url, $content = null, array $headers = [], $method = null)
    {
        $headers += ['Content-Type' => 'application/x-www-form-urlencoded'];

        return parent::httpRequest($url, $content, $headers, $method);
    }

    private function handleCsrfToken(): void
    {
        if (null === $this->state->getCsrfToken()) {
            $this->state->setCsrfToken(NonceGenerator::generate());
        }

        $this->storage->save($this, $this->state->getCsrfToken(), 'csrf_state');
    }
}
