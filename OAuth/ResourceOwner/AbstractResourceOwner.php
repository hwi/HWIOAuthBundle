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

use Http\Client\Common\HttpMethodsClient;
use Http\Client\Exception;
use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * AbstractResourceOwner.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 * @author Francisco Facioni <fran6co@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
abstract class AbstractResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var HttpMethodsClient
     */
    protected $httpClient;

    /**
     * @var HttpUtils
     */
    protected $httpUtils;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var RequestDataStorageInterface
     */
    protected $storage;

    /**
     * @param HttpMethodsClient           $httpClient Httplug client
     * @param HttpUtils                   $httpUtils  Http utils
     * @param array                       $options    Options for the resource owner
     * @param string                      $name       Name for the resource owner
     * @param RequestDataStorageInterface $storage    Request token storage
     */
    public function __construct(
        HttpMethodsClient $httpClient,
        HttpUtils $httpUtils,
        array $options,
        $name,
        RequestDataStorageInterface $storage
    ) {
        $this->httpClient = $httpClient;
        $this->httpUtils = $httpUtils;
        $this->name = $name;
        $this->storage = $storage;

        if (!empty($options['paths'])) {
            $this->addPaths($options['paths']);
        }
        unset($options['paths']);

        if (!empty($options['options'])) {
            $options += $options['options'];
            unset($options['options']);
        }
        unset($options['options']);

        // Resolve merged options
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        $this->configure();
    }

    /**
     * Gives a chance for extending providers to customize stuff.
     */
    public function configure()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException(sprintf('Unknown option "%s"', $name));
        }

        return $this->options[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function addPaths(array $paths)
    {
        $this->paths = array_merge($this->paths, $paths);
    }

    /**
     * Retrieve an access token for a given code.
     *
     * @param HttpRequest $request         The request object from where the code is going to extracted
     * @param mixed       $redirectUri     The uri to redirect the client back to
     * @param array       $extraParameters An array of parameters to add to the url
     *
     * @throws AuthenticationException If an OAuth error occurred or no access token is found
     * @throws HttpTransportException
     *
     * @return array array containing the access token and it's 'expires_in' value,
     *               along with any other parameters returned from the authentication
     *               provider
     */
    abstract public function getAccessToken(HttpRequest $request, $redirectUri, array $extraParameters = []);

    /**
     * Refresh an access token using a refresh token.
     *
     * @param string $refreshToken    Refresh token
     * @param array  $extraParameters An array of parameters to add to the url
     *
     * @throws AuthenticationException If an OAuth error occurred or no access token is found
     * @throws HttpTransportException
     *
     * @return array array containing the access token and it's 'expires_in' value,
     *               along with any other parameters returned from the authentication
     *               provider
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = [])
    {
        throw new AuthenticationException('OAuth error: "Method unsupported."');
    }

    /**
     * Revoke an OAuth access token or refresh token.
     *
     * @param string $token the token (access token or a refresh token) that should be revoked
     *
     * @throws AuthenticationException If an OAuth error occurred
     * @throws HttpTransportException
     *
     * @return bool returns True if the revocation was successful, otherwise False
     */
    public function revokeToken($token)
    {
        throw new AuthenticationException('OAuth error: "Method unsupported."');
    }

    /**
     * Get the response object to return.
     *
     * @return UserResponseInterface
     */
    protected function getUserResponse()
    {
        $response = new $this->options['user_response_class']();
        if ($response instanceof PathUserResponse) {
            $response->setPaths($this->paths);
        }

        return $response;
    }

    /**
     * @param string $url
     * @param array  $parameters
     *
     * @return string
     */
    protected function normalizeUrl($url, array $parameters = [])
    {
        $normalizedUrl = $url;
        if (!empty($parameters)) {
            $normalizedUrl .= (false !== strpos($url, '?') ? '&' : '?').http_build_query($parameters, '', '&');
        }

        return $normalizedUrl;
    }

    /**
     * Performs an HTTP request.
     *
     * @param string       $url     The url to fetch
     * @param string|array $content The content of the request
     * @param array        $headers The headers of the request
     * @param string       $method  The HTTP method to use
     *
     * @throws HttpTransportException
     *
     * @return ResponseInterface The response content
     */
    protected function httpRequest($url, $content = null, array $headers = [], $method = null)
    {
        if (null === $method) {
            $method = null === $content || '' === $content ? 'GET' : 'POST';
        }

        $headers += array('User-Agent' => 'HWIOAuthBundle (https://github.com/hwi/HWIOAuthBundle)');
        if (is_string($content)) {
            $headers += array('Content-Length' => strlen($content));
        } elseif (is_array($content)) {
            $content = http_build_query($content, '', '&');
        }

        try {
            return $this->httpClient->send(
                $method,
                $url,
                $headers,
                $content
            );
        } catch (Exception $e) {
            throw new HttpTransportException('Error while sending HTTP request', $this->getName(), $e->getCode(), $e);
        }
    }

    /**
     * Get the 'parsed' content based on the response headers.
     *
     * @param ResponseInterface $rawResponse
     *
     * @return array
     */
    protected function getResponseContent(ResponseInterface $rawResponse)
    {
        // First check that content in response exists, due too bug: https://bugs.php.net/bug.php?id=54484
        $content = (string) $rawResponse->getBody();
        if (!$content) {
            return array();
        }

        $response = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            parse_str($content, $response);
        }

        return $response;
    }

    /**
     * Generate a non-guessable nonce value.
     *
     * @return string
     */
    protected function generateNonce()
    {
        return md5(microtime(true).uniqid('', true));
    }

    /**
     * @param string $url
     * @param array  $parameters
     *
     * @throws HttpTransportException
     *
     * @return ResponseInterface
     */
    abstract protected function doGetTokenRequest($url, array $parameters = []);

    /**
     * @param string $url
     * @param array  $parameters
     *
     * @throws HttpTransportException
     *
     * @return ResponseInterface
     */
    abstract protected function doGetUserInformationRequest($url, array $parameters = []);

    /**
     * Configure the option resolver.
     *
     * @param OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'client_id',
            'client_secret',
            'authorization_url',
            'access_token_url',
            'infos_url',
        ]);

        $resolver->setDefaults([
            'scope' => null,
            'csrf' => false,
            'user_response_class' => PathUserResponse::class,
            'auth_with_one_url' => false,
        ]);

        $resolver->setAllowedValues('csrf', [true, false]);
    }
}
