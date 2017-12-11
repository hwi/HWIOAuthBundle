<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\MessageFactoryDiscovery;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\HttpUtils;

abstract class ResourceOwnerTestCase extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|HttpMethodsClient */
    protected $httpClient;
    protected $httpResponse;
    protected $httpResponseContentType;
    protected $httpResponseHttpCode = 200;
    protected $httpClientCalls;

    /** @var \PHPUnit_Framework_MockObject_MockObject|RequestDataStorageInterface */
    protected $storage;
    protected $state = 'random';
    protected $csrf = false;

    protected $options = array();
    protected $paths = array();

    protected $resourceOwnerClass;

    protected function mockHttpClient($response = '', $contentType = 'text/plain')
    {
        if (null !== $this->httpClientCalls) {
            $mock = $this->httpClient->expects($this->exactly($this->httpClientCalls));
        } else {
            $mock = $this->httpClient->expects($this->once());
        }

        $mock->method('send')
            ->will($this->returnCallback(function ($method, $uri, array $headers = [], $body = null) use ($response, $contentType) {
                $headers += array(
                    'Content-Type' => $contentType ?: $this->httpResponseContentType,
                );

                return MessageFactoryDiscovery::find()
                    ->createResponse(
                        $this->httpResponseHttpCode,
                        null,
                        $headers,
                        $response ?: $this->httpResponse
                    )
                ;
            }));
    }

    protected function createResourceOwner($name, array $options = array(), array $paths = array())
    {
        $this->httpClient = $this->getMockBuilder(HttpMethodsClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storage = $this->getMockBuilder(RequestDataStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var HttpUtils $httpUtils */
        $httpUtils = $this->getMockBuilder(HttpUtils::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resourceOwner = $this->setUpResourceOwner($name, $httpUtils, array_merge($this->options, $options));
        $resourceOwner->addPaths(array_merge($this->paths, $paths));

        return $resourceOwner;
    }

    /**
     * @param string    $name
     * @param HttpUtils $httpUtils
     * @param array     $options
     *
     * @return \HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface
     */
    protected function setUpResourceOwner($name, HttpUtils $httpUtils, array $options)
    {
        if (!$this->resourceOwnerClass) {
            throw new \RuntimeException('Missing resource owner class declaration!');
        }

        if (!in_array(ResourceOwnerInterface::class, class_implements($this->resourceOwnerClass), true)) {
            throw new \RuntimeException('Class is not implementing "ResourceOwnerInterface"!');
        }

        return new $this->resourceOwnerClass($this->httpClient, $httpUtils, $options, $name, $this->storage);
    }
}
