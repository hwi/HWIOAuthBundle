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

use Buzz\Client\ClientInterface as HttpClientInterface,
    Buzz\Message\Request as HttpRequest,
    Buzz\Message\Response as HttpResponse;

use Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\Security\Http\HttpUtils;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface,
    HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;

/**
 * GenericOAuth1ResourceOwner
 *
 * @author Francisco Facioni <fran6co@gmail.com>
 */
class GenericOAuth1ResourceOwner implements ResourceOwnerInterface
{
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
     * @var array
     */
    protected $paths = array();

    /**
     * @var Buzz\Client\ClientInterface
     */
    protected $httpClient;

    /**
     * @access string
     */
    protected $name;

    /**
     * @param HttpClientInterface $httpClient Buzz http client
     * @param HttpUtils           $httpUtils  Http utils
     * @param array               $options    Options for the resource owner
     * @param string              $name       Name for the resource owner
     * @param array               $paths      Optional paths to use for the default response
     */
    public function __construct(HttpClientInterface $httpClient, HttpUtils $httpUtils, array $options, $name, $paths = array())
    {
        $this->options = array_merge($this->options, $options);
        $this->paths = array_merge($this->paths, $paths);

        $this->httpClient = $httpClient;
        $this->httpUtils  = $httpUtils;
        $this->name       = $name;

        $this->configure();
    }

    /**
     * Gives a chance for extending providers to customize stuff
     */
    public function configure()
    {

    }

    /**
     * Retrieve an option by name
     *
     * @param string $name The option name
     *
     * @return mixed The option value
     *
     * @throws InvalidArgumentException When the option does not exist
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException(sprintf('Unknown option "%s"', $name));
        }

        return $this->options[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInformation($accessToken)
    {
        $url = $this->getOption('infos_url');
        $url .= (false !== strpos($url, '?') ? '&' : '?').http_build_query(array(
            'access_token' => $accessToken
        ));

        $response = $this->getUserResponse();
        $response->setResponse($this->httpRequest($url)->getContent());
        $response->setResourceOwner($this);

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
    public function getRequestToken($redirectUri, array $extraParameters = array())
    {
        $parameters = array_merge($extraParameters, array(
            'oauth_consumer_key'     => $this->getOption('client_id'),
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_callback'         => $redirectUri,
            'oauth_signature_method' => 'HMAC-SHA1',
        ));

        $url = $this->getOption('request_token_url');
        $parameters['oauth_signature'] = $this->signRequest($url, $parameters);

        $apiResponse = $this->httpRequest($url, $parameters);

        if (false !== strpos($apiResponse->getHeader('Content-Type'), 'application/json')) {
            $response = json_decode($apiResponse->getContent(), true);
        } else {
            parse_str($apiResponse->getContent(), $response);
        }

        if (isset($response['oauth_callback_confirmed']) && ($response['oauth_callback_confirmed'] != 'true')) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['error']));
        }

        if (!isset($response['oauth_token']) || !isset($response['oauth_token_secret'])) {
            throw new AuthenticationException('Not a valid request token.');
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessToken($code, $redirectUri, array $extraParameters = array())
    {
        $token = $this->getRequestToken($redirectUri, $extraParameters);

        $parameters = array_merge($extraParameters, array(
            'oauth_consumer_key'     => $this->getOption('client_id'),
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token'            => $token["oauth_token"],
            'oauth_verifier'         => $code,
        ));

        $url = $this->getOption('access_token_url');
        $parameters['oauth_signature'] = $this->signRequest($url, $parameters, $token["oauth_token_secret"]);

        $apiResponse = $this->httpRequest($url, $parameters);

        if (false !== strpos($apiResponse->getHeader('Content-Type'), 'application/json')) {
            $response = json_decode($apiResponse->getContent(), true);
        } else {
            parse_str($apiResponse->getContent(), $response);
        }

        if (isset($response['error'])) {
            throw new AuthenticationException(sprintf('OAuth error: "%s"', $response['error']));
        }

        if (!isset($response['access'])) {
            throw new AuthenticationException('Not a valid access token.');
        }

        return $response['access'];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getCodeFieldName()
    {
        return "oauth_verifier";
    }

    protected function generateNonce()
    {
        $mt = microtime();
        $rand = mt_rand();

        return md5($mt . $rand);
    }

    protected function signRequest($url, $parameters, $tokenSecret = '')
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
            'POST',
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

    /**
     * Get the response object to return.
     *
     * @return UserResponseInterface
     */
    protected function getUserResponse()
    {
        $response = new $this->options['user_response_class'];

        if ($response instanceof PathUserResponse) {
            $response->setPaths($this->paths);
        }

        return $response;
    }

    /**
     * Performs an HTTP request
     *
     * @param string $url     The url to fetch
     * @param array  $content The content of the request
     * @param string $method  The HTTP method to use
     *
     * @return string The response content
     */
    protected function httpRequest($url, $content = array(), $method = null)
    {
        if (null === $method) {
            $method = empty($content) ? HttpRequest::METHOD_GET : HttpRequest::METHOD_POST;
        }

        $request  = new HttpRequest($method, $url);
        $response = new HttpResponse();

        $authorization = 'Authorization: OAuth';
        if (null !== $this->getOption('realm')) {
            $authorization = 'Authorization: OAuth realm="' . rawurlencode($this->getOption('realm')) . '"';
        }

        foreach ($content as $key => $value) {
            $value = rawurlencode($value);
            $authorization .= ", $key=\"$value\"";
        }

        $request->addHeader($authorization);

        $this->httpClient->send($request, $response);

        return $response;
    }
}
