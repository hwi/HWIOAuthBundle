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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface,
    HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse,
    HWI\Bundle\OAuthBundle\OAuth\OAuth1RequestTokenStorageInterface;

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
        'displayname_path' => '',
        'infos_url' => '',
        'user_response_class' => 'HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
        'username_path' => '',
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
        $parameters['oauth_signature'] = $this->signRequest('GET', $url, $parameters, $accessToken['oauth_token_secret']);

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
        $parameters['oauth_signature'] = $this->signRequest('POST', $url, $parameters, $requestToken['oauth_token_secret']);

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
        $parameters['oauth_signature'] = $this->signRequest('POST', $url, $parameters);

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
        $authorization = 'Authorization: OAuth';
        if (null !== $this->getOption('realm')) {
            $authorization = 'Authorization: OAuth realm="' . rawurlencode($this->getOption('realm')) . '"';
        }

        foreach ($parameters as $key => $value) {
            $value = rawurlencode($value);
            $authorization .= ", $key=\"$value\"";
        }

        $headers[] = $authorization;

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

    /**
     * Sign the request parameters
     *
     * @param string $method Request method
     * @param string $url    Request url
     * @param array  $parameters  Parameters for the request
     * @param string $tokenSecret Optional token secret to use with signing
     *
     * @return string
     */
    protected function signRequest($method, $url, $parameters, $tokenSecret = '')
    {
        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($parameters, 'strcmp');

        $parts = array(
            $method,
            rawurlencode($url),
            rawurlencode(http_build_query($parameters)),
        );

        $baseString = implode('&', $parts);

        $keyParts = array(
            rawurlencode($this->getOption('client_secret')),
            rawurlencode($tokenSecret),
        );

        $key = implode('&', $keyParts);

        return base64_encode(hash_hmac('sha1', $baseString, $key, true));
    }
}
