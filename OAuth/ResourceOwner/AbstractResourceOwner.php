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
    HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse,
    HWI\Bundle\OAuthBundle\OAuth\StorageInterface;

/**
 * AbstractResourceOwner
 *
 * @author Francisco Facioni <fran6co@gmail.com>
 */
abstract class AbstractResourceOwner implements ResourceOwnerInterface
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
     * @var HWI\Bundle\OAuthBundle\OAuth\StorageInterface
     */
    protected $storage;

    /**
     * @access string
     */
    protected $name;

    /**
     * @param HttpClientInterface $httpClient Buzz http client
     * @param HttpUtils           $httpUtils  Http utils
     * @param StorageInterface    $storage    Storage container
     * @param array               $options    Options for the resource owner
     * @param string              $name       Name for the resource owner
     * @param array               $paths      Optional paths to use for the default response
     */
    public function __construct(HttpClientInterface $httpClient, HttpUtils $httpUtils,StorageInterface $storage, array $options, $name, $paths = array())
    {
        $this->options = array_merge($this->options, $options);
        $this->paths = array_merge($this->paths, $paths);

        $this->httpClient = $httpClient;
        $this->httpUtils  = $httpUtils;
        $this->storage    = $storage;
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
        $response = new $this->options['user_response_class'];

        if ($response instanceof PathUserResponse) {
            $response->setPaths($this->paths);
        }

        return $response;
    }
    /**
     *
     * Performs an HTTP request
     *
     * @param string $url     The url to fetch
     * @param string $content The content of the request
     * @param array  $headers The headers of the request
     * @param string $method  The HTTP method to use
     *
     * @return string The response content
     */
    protected function httpRequest($url, $content = null, $headers = array(), $method = null)
    {
        if (null === $method) {
            $method = null === $content ? HttpRequest::METHOD_GET : HttpRequest::METHOD_POST;
        }

        $request  = new HttpRequest($method, $url);
        $response = new HttpResponse();

        $request->setHeaders($headers);
        $request->setContent($content);

        $this->httpClient->send($request, $response);

        return $response;
    }
}
