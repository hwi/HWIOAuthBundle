<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\OAuthErrorHandler;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * GenericOAuth1ResourceOwner.
 *
 * @author Francisco Facioni <fran6co@gmail.com>
 */
class GenericOAuth1ResourceOwner extends AbstractResourceOwner
{
    /**
     * {@inheritdoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $parameters = array_merge([
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token' => $accessToken['oauth_token'],
        ], $extraParameters);

        $url = $this->options['infos_url'];
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            'GET',
            $url,
            $parameters,
            $this->options['client_secret'],
            $accessToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        $content = $this->doGetUserInformationRequest($url, $parameters);

        $response = $this->getUserResponse();
        $response->setData($content instanceof ResponseInterface ? (string) $content->getBody() : $content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        return $this->normalizeUrl($this->options['authorization_url'], array('oauth_token' => $token['oauth_token']));
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(HttpRequest $request, $redirectUri, array $extraParameters = array())
    {
        OAuthErrorHandler::handleOAuthError($request);

        try {
            if (null === $requestToken = $this->storage->fetch($this, $request->query->get('oauth_token'))) {
                throw new \RuntimeException('No request token found in the storage.');
            }
        } catch (\InvalidArgumentException $e) {
            throw new AuthenticationException('Given token is not valid.');
        }

        $parameters = array_merge(array(
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token' => $requestToken['oauth_token'],
            'oauth_verifier' => $request->query->get('oauth_verifier'),
        ), $extraParameters);

        $url = $this->options['access_token_url'];
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            'POST',
            $url,
            $parameters,
            $this->options['client_secret'],
            $requestToken['oauth_token_secret'],
            $this->options['signature_method']
        );

        $response = $this->doGetTokenRequest($url, $parameters);
        $response = $this->getResponseContent($response);

        if (isset($response['oauth_problem'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['oauth_problem']));
        }

        if (!isset($response['oauth_token'], $response['oauth_token_secret'])) {
            throw new AuthenticationException('Not a valid request token.');
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function handles(HttpRequest $request)
    {
        return $request->query->has('oauth_token');
    }

    /**
     * {@inheritdoc}
     */
    public function isCsrfTokenValid($csrfToken)
    {
        // OAuth1.0a passes token with every call
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestToken($redirectUri, array $extraParameters = array())
    {
        $timestamp = time();

        $parameters = array_merge([
            'oauth_consumer_key' => $this->options['client_id'],
            'oauth_timestamp' => $timestamp,
            'oauth_nonce' => $this->generateNonce(),
            'oauth_version' => '1.0',
            'oauth_callback' => $redirectUri,
            'oauth_signature_method' => $this->options['signature_method'],
        ], $extraParameters);

        $url = $this->options['request_token_url'];
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            'POST',
            $url,
            $parameters,
            $this->options['client_secret'],
            '',
            $this->options['signature_method']
        );

        $apiResponse = $this->httpRequest($url, null, array(), 'POST', $parameters);

        $response = $this->getResponseContent($apiResponse);

        if (isset($response['oauth_problem'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['oauth_problem']));
        }

        if (isset($response['oauth_callback_confirmed']) && 'true' !== $response['oauth_callback_confirmed']) {
            throw new AuthenticationException('Defined OAuth callback was not confirmed.');
        }

        if (!isset($response['oauth_token'], $response['oauth_token_secret'])) {
            throw new AuthenticationException('Not a valid request token.');
        }

        $response['timestamp'] = $timestamp;

        $this->storage->save($this, $response);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, null, array(), 'POST', $parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, null, array(), null, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function httpRequest($url, $content = null, array $headers = array(), $method = null, array $parameters = array())
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $key.'="'.rawurlencode($value).'"';
        }

        if (!$this->options['realm']) {
            array_unshift($parameters, 'realm="'.rawurlencode($this->options['realm']).'"');
        }

        $headers['Authorization'] = 'OAuth '.implode(', ', $parameters);

        return parent::httpRequest($url, $content, $headers, $method);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired([
            'request_token_url',
        ]);

        $resolver->setDefaults([
            'realm' => null,
            'signature_method' => 'HMAC-SHA1',
        ]);

        $resolver->setAllowedValues('signature_method', ['HMAC-SHA1', 'RSA-SHA1', 'PLAINTEXT']);
    }
}
