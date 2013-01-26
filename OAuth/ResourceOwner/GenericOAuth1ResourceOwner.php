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

use Buzz\Client\ClientInterface as HttpClientInterface;

use Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\Security\Http\HttpUtils,
    Symfony\Component\HttpFoundation\Request;

use HWI\Bundle\OAuthBundle\OAuth\OAuth1RequestTokenStorageInterface,
    HWI\Bundle\OAuthBundle\Security\OAuthUtils;

/**
 * GenericOAuth1ResourceOwner
 *
 * @author Francisco Facioni <fran6co@gmail.com>
 */
class GenericOAuth1ResourceOwner extends AbstractResourceOwner
{
    /**
     * @var OAuth1RequestTokenStorageInterface
     */
    protected $storage;

    /**
     * @var array
     */
    protected $options = array(
        'client_id' => '',
        'client_secret' => '',
        'infos_url' => '',
        'user_response_class' => 'HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
        'realm' => null,
    );

    /**
     * @param HttpClientInterface                $httpClient Buzz http client
     * @param HttpUtils                          $httpUtils  Http utils
     * @param array                              $options    Options for the resource owner
     * @param string                             $name       Name for the resource owner
     * @param OAuth1RequestTokenStorageInterface $storage Request token storage
     */
    public function __construct(HttpClientInterface $httpClient, HttpUtils $httpUtils, array $options, $name, OAuth1RequestTokenStorageInterface $storage)
    {
        parent::__construct($httpClient, $httpUtils, $options, $name);

        $this->storage = $storage;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation($accessToken)
    {
        $parameters = array(
            'oauth_consumer_key'     => $this->getOption('client_id'),
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token'            => $accessToken['oauth_token'],
        );

        $url = $this->getOption('infos_url');
        $parameters['oauth_signature'] = OAuthUtils::signRequest('GET', $url, $parameters, $this->getOption('client_secret'), $accessToken['oauth_token_secret']);

        $content = $this->doGetUserInformationRequest($url, $parameters)->getContent();

        $response = $this->getUserResponse();
        $response->setResponse($content);
        $response->setResourceOwner($this);
        $response->setAccessToken($accessToken);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        return $this->getOption('authorization_url').'?'.http_build_query(array('oauth_token' => $token['oauth_token']));
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        if (null === $requestToken = $this->storage->fetch($this, $request->query->get('oauth_token'))) {
            throw new \RuntimeException('No request token found in the storage.');
        }

        $parameters = array_merge($extraParameters, array(
            'oauth_consumer_key'     => $this->getOption('client_id'),
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token'            => $requestToken['oauth_token'],
            'oauth_verifier'         => $request->query->get('oauth_verifier'),
        ));

        $url = $this->getOption('access_token_url');
        $parameters['oauth_signature'] = OAuthUtils::signRequest('POST', $url, $parameters, $this->getOption('client_secret'), $requestToken['oauth_token_secret']);

        $response = $this->doGetAccessTokenRequest($url, $parameters);
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

        $parameters = array_merge($extraParameters, array(
            'oauth_consumer_key'     => $this->getOption('client_id'),
            'oauth_timestamp'        => $timestamp,
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_callback'         => $redirectUri,
            'oauth_signature_method' => 'HMAC-SHA1',
        ));

        $url = $this->getOption('request_token_url');
        $parameters['oauth_signature'] = OAuthUtils::signRequest('POST', $url, $parameters, $this->getOption('client_secret'));

        $apiResponse = $this->httpRequest($url, null, $parameters, array(), 'POST');

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
     * Generate a non-guessable nonce value.
     *
     * @return string
     */
    protected function generateNonce()
    {
        return md5(microtime() . mt_rand());
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
    protected function doGetAccessTokenRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, null, $parameters, array(), 'POST');
    }

    /**
     * {@inheritDoc}
     */
    protected function doGetUserInformationRequest($url, array $parameters = array())
    {
        return $this->httpRequest($url, null, $parameters, array(), 'GET');
    }
}
