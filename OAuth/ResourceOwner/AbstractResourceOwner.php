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
use Buzz\Message\MessageInterface as HttpMessageInterface;
use Buzz\Message\Request as HttpRequest;
use Buzz\Message\RequestInterface as HttpRequestInterface;
use Buzz\Message\Response as HttpResponse;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * AbstractResourceOwner
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 * @author Francisco Facioni <fran6co@gmail.com>
 */
abstract class AbstractResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $defaultOptions = array();

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
     * @var string
     */
    protected $name;

    /**
     * @param HttpClientInterface $httpClient Buzz http client
     * @param HttpUtils           $httpUtils  Http utils
     * @param array               $options    Options for the resource owner
     * @param string              $name       Name for the resource owner
     */
    public function __construct(HttpClientInterface $httpClient, HttpUtils $httpUtils, array $options, $name)
    {
        $this->httpClient = $httpClient;
        $this->httpUtils  = $httpUtils;
        $this->name       = $name;

        $this->addOptions($options);
        $this->configure();
    }

    /**
     * Gives a chance for extending providers to customize stuff
     */
    public function configure()
    {

    }

    /**
     * Add (extra) options to the configuration.
     *
     * @param array $options
     */
    public function addOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            if (array_key_exists($name, $this->defaultOptions)) {
                return $this->defaultOptions[$name];
            }

            throw new \InvalidArgumentException(sprintf('Unknown option "%s"', $name));
        }

        return $this->options[$name];
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
     * Get the response object to return.
     *
     * @return UserResponseInterface
     */
    protected function getUserResponse()
    {
        $response = $this->getOption('user_response_class');
        $response = new $response;

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
        $normalizedUrl  = $url;
        $normalizedUrl .= (false !== strpos($url, '?') ? '&' : '?').http_build_query($parameters);

        return $normalizedUrl;
    }

    /**
     * Performs an HTTP request
     *
     * @param string $url     The url to fetch
     * @param string $content The content of the request
     * @param array  $headers The headers of the request
     * @param string $method  The HTTP method to use
     *
     * @return HttpMessageInterface The response content
     */
    protected function httpRequest($url, $content = null, $headers = array(), $method = null)
    {
        if (null === $method) {
            $method = null === $content ? HttpRequestInterface::METHOD_GET : HttpRequestInterface::METHOD_POST;
        }

        $request  = new HttpRequest($method, $url);
        $response = new HttpResponse();

        $headers = array_merge(
            array(
                'User-Agent: HWIOAuthBundle (https://github.com/hwi/HWIOAuthBundle)',
            ),
            $headers
        );

        $request->setHeaders($headers);
        $request->setContent($content);

        $this->httpClient->send($request, $response);

        return $response;
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
     * Get the 'parsed' content based on the response headers.
     *
     * @param HttpMessageInterface $rawResponse
     *
     * @return mixed
     */
    protected function getResponseContent(HttpMessageInterface $rawResponse)
    {
        if (false !== strpos($rawResponse->getHeader('Content-Type'), 'application/json')) {
            $response = json_decode($rawResponse->getContent(), true);
        } else {
            parse_str($rawResponse->getContent(), $response);
        }

        return $response;
    }

    /**
     * @param string $url
     * @param array  $parameters
     *
     * @return mixed
     */
    abstract protected function doGetAccessTokenRequest($url, array $parameters = array());

    /**
     * @param string $url
     * @param array  $parameters
     *
     * @return mixed
     */
    abstract protected function doGetUserInformationRequest($url, array $parameters = array());
}
