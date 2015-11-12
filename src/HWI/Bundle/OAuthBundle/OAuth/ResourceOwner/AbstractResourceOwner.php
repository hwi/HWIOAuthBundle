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
use Buzz\Exception\ClientException;
use Buzz\Message\MessageInterface as HttpMessageInterface;
use Buzz\Message\Request as HttpRequest;
use Buzz\Message\RequestInterface as HttpRequestInterface;
use Buzz\Message\Response as HttpResponse;
use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * AbstractResourceOwner
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
    protected $options = array();

    /**
     * @var array
     */
    protected $paths = array();

    /**
     * @var HttpClientInterface
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
     * @param HttpClientInterface         $httpClient Buzz http client
     * @param HttpUtils                   $httpUtils  Http utils
     * @param array                       $options    Options for the resource owner
     * @param string                      $name       Name for the resource owner
     * @param RequestDataStorageInterface $storage    Request token storage
     */
    public function __construct(HttpClientInterface $httpClient, HttpUtils $httpUtils, array $options, $name, RequestDataStorageInterface $storage)
    {
        $this->httpClient = $httpClient;
        $this->httpUtils  = $httpUtils;
        $this->name       = $name;
        $this->storage    = $storage;

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
        $options = $resolver->resolve($options);
        $this->options = $options;

        $this->configure();
    }

    /**
     * Gives a chance for extending providers to customize stuff
     */
    public function configure()
    {

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
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException(sprintf('Unknown option "%s"', $name));
        }

        return $this->options[$name];
    }

    /**
     * Add extra paths to the configuration.
     *
     * @param array $paths
     */
    public function addPaths(array $paths)
    {
        $this->paths = array_merge($this->paths, $paths);
    }

    /**
     * Refresh an access token using a refresh token.
     *
     * @param string $refreshToken    Refresh token
     * @param array  $extraParameters An array of parameters to add to the url
     *
     * @return array Array containing the access token and it's 'expires_in' value,
     *               along with any other parameters returned from the authentication
     *               provider.
     *
     * @throws AuthenticationException If an OAuth error occurred or no access token is found
     */
    public function refreshAccessToken($refreshToken, array $extraParameters = array())
    {
        throw new AuthenticationException('OAuth error: "Method unsupported."');
    }

    /**
     * Revoke an OAuth access token or refresh token.
     *
     * @param string $token The token (access token or a refresh token) that should be revoked.
     *
     * @return Boolean Returns True if the revocation was successful, otherwise False.
     *
     * @throws AuthenticationException If an OAuth error occurred
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
        $response = new $this->options['user_response_class'];
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
    protected function normalizeUrl($url, array $parameters = array())
    {
        $normalizedUrl = $url;
        if (!empty($parameters)) {
            $normalizedUrl .= (false !== strpos($url, '?') ? '&' : '?').http_build_query($parameters, '', '&');
        }

        return $normalizedUrl;
    }

    /**
     * Performs an HTTP request
     *
     * @param string $url           The url to fetch
     * @param string|array $content The content of the request
     * @param array  $headers       The headers of the request
     * @param string $method        The HTTP method to use
     *
     * @return HttpResponse The response content
     */
    protected function httpRequest($url, $content = null, $headers = array(), $method = null)
    {
        if (null === $method) {
            $method = null === $content || '' === $content ? HttpRequestInterface::METHOD_GET : HttpRequestInterface::METHOD_POST;
        }

        $request  = new HttpRequest($method, $url);
        $response = new HttpResponse();

        $contentLength = 0;
        if (is_string($content)) {
            $contentLength = strlen($content);
        } elseif (is_array($content)) {
            $contentLength = strlen(implode('', $content));
        }

        $headers = array_merge(
            array(
                'User-Agent: HWIOAuthBundle (https://github.com/hwi/HWIOAuthBundle)',
                'Content-Length: ' . $contentLength,
            ),
            $headers
        );

        $request->setHeaders($headers);
        $request->setContent($content);

        try {
            $this->httpClient->send($request, $response);
        } catch (ClientException $e) {
            throw new HttpTransportException('Error while sending HTTP request', $this->getName(), $e->getCode(), $e);
        }

        return $response;
    }

    /**
     * Get the 'parsed' content based on the response headers.
     *
     * @param HttpMessageInterface $rawResponse
     *
     * @return array
     */
    protected function getResponseContent(HttpMessageInterface $rawResponse)
    {
        // First check that content in response exists, due too bug: https://bugs.php.net/bug.php?id=54484
        $content = $rawResponse->getContent();
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
     * @return HttpResponse
     */
    abstract protected function doGetTokenRequest($url, array $parameters = array());

    /**
     * @param string $url
     * @param array  $parameters
     *
     * @return HttpResponse
     */
    abstract protected function doGetUserInformationRequest($url, array $parameters = array());

    /**
     * Configure the option resolver
     *
     * @param OptionsResolverInterface $resolver
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array(
            'client_id',
            'client_secret',
            'authorization_url',
            'access_token_url',
            'infos_url',
        ));

        $resolver->setDefaults(array(
            'scope'               => null,
            'csrf'                => false,
            'user_response_class' => 'HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',
            'auth_with_one_url'   => false,
        ));

        if (method_exists($resolver, 'setDefined')) {
            $resolver->setAllowedValues('csrf', array(true, false));
        } else {
            $resolver->setAllowedValues(array(
                'csrf' => array(true, false),
            ));
        }
    }
}
