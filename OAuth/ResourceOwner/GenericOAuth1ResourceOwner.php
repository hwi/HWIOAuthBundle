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

use Buzz\Message\RequestInterface as HttpRequestInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * GenericOAuth1ResourceOwner
 *
 * @author Francisco Facioni <fran6co@gmail.com>
 */
class GenericOAuth1ResourceOwner extends AbstractResourceOwner
{
    /**
     * @var array
     */
    protected $defaultOptions = array(
        'client_id'           => null,
        'client_secret'       => null,

        'infos_url'           => null,

        'user_response_class' => 'HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',

        'realm'               => null,
        'scope'               => null,

        'csrf'                => false,

        'signature_method'    => 'HMAC-SHA1',
    );

    /**
     * {@inheritDoc}
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        return $this->getCustomInformation($accessToken, $this->getOption('infos_url'), $extraParameters);
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomInformation(array $accessToken, $url, array $extraParameters = array())
    {
        $parameters = array_merge(array(
            'oauth_consumer_key'     => $this->getOption('client_id'),
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_signature_method' => $this->getOption('signature_method'),
            'oauth_token'            => $accessToken['oauth_token'],
        ), $extraParameters);

        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            HttpRequestInterface::METHOD_GET,
            $url,
            $parameters,
            $this->getOption('client_secret'),
            $accessToken['oauth_token_secret'],
            $this->getOption('signature_method')
        );

        $content = $this->doGetUserInformationRequest($url, $parameters)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setOAuthToken(new OAuthToken($accessToken));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        return $this->normalizeUrl($this->getOption('authorization_url'), array('oauth_token' => $token['oauth_token']));
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        if (null === $requestToken = $this->storage->fetch($this, $request->query->get('oauth_token'))) {
            throw new \RuntimeException('No request token found in the storage.');
        }

        $parameters = array_merge(array(
            'oauth_consumer_key'     => $this->getOption('client_id'),
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_signature_method' => $this->getOption('signature_method'),
            'oauth_token'            => $requestToken['oauth_token'],
            'oauth_verifier'         => $request->query->get('oauth_verifier'),
        ), $extraParameters);

        $url = $this->getOption('access_token_url');
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            HttpRequestInterface::METHOD_POST,
            $url,
            $parameters,
            $this->getOption('client_secret'),
            $requestToken['oauth_token_secret'],
            $this->getOption('signature_method')
        );

        $response = $this->doGetTokenRequest($url, $parameters);
        $response = $this->getResponseContent($response);

        if (isset($response['oauth_problem'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['oauth_problem']));
        }

        if (!isset($response['oauth_token']) || !isset($response['oauth_token_secret'])) {
            throw new AuthenticationException('Not a valid request token.');
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function handles(Request $request)
    {
        return $request->query->has('oauth_token');
    }

    /**
     * {@inheritDoc}
     */
    protected function getRequestToken($redirectUri, array $extraParameters = array())
    {
        $timestamp = time();

        $parameters = array_merge(array(
            'oauth_consumer_key'     => $this->getOption('client_id'),
            'oauth_timestamp'        => $timestamp,
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_callback'         => $redirectUri,
            'oauth_signature_method' => $this->getOption('signature_method'),
        ), $extraParameters);

        $url = $this->getOption('request_token_url');
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            HttpRequestInterface::METHOD_POST,
            $url,
            $parameters,
            $this->getOption('client_secret'),
            '',
            $this->getOption('signature_method')
        );

        $apiResponse = $this->httpRequest($url, null, $parameters, array(), HttpRequestInterface::METHOD_POST);

        $response = $this->getResponseContent($apiResponse);

        if (isset($response['oauth_problem']) || (isset($response['oauth_callback_confirmed']) && ($response['oauth_callback_confirmed'] != 'true'))) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['oauth_problem']));
        }

        if (!isset($response['oauth_token']) || !isset($response['oauth_token_secret'])) {
            throw new AuthenticationException('Not a valid request token.');
        }

        $response['timestamp'] = $timestamp;

        $this->storage->save($this, $response);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    protected function httpRequest($url, $content = null, $parameters = array(), $headers = array(), $method = null)
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $key . '="' . rawurlencode($value) . '"';
        }

        if (!$this->getOption('realm')) {
            array_unshift($parameters, 'realm="' . rawurlencode($this->getOption('realm')) . '"');
        }

        $headers[] = 'Authorization: OAuth ' . implode(', ', $parameters);

        return parent::httpRequest($url, $content, $headers, $method);
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, null, $parameters, array(), HttpRequestInterface::METHOD_POST);
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, null, $parameters);
    }
}
