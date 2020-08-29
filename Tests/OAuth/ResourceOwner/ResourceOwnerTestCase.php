<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use Http\Client\Common\HttpMethodsClient;
use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Security\Http\HttpUtils;

abstract class ResourceOwnerTestCase extends TestCase
{
    /** @var MockObject|HttpClient */
    protected $httpClient;
    protected $httpResponse;
    protected $httpResponseContentType;
    protected $httpResponseHttpCode = 200;
    protected $httpClientCalls;

    /** @var MockObject|RequestDataStorageInterface */
    protected $storage;
    protected $state = 'eyJzdGF0ZSI6InJhbmRvbSJ9';
    protected $csrf = false;

    protected $options = [];
    protected $paths = [];

    protected $resourceOwnerClass;

    protected function mockHttpClient($response = '', $contentType = 'text/plain')
    {
        if (null !== $this->httpClientCalls) {
            $mock = $this->httpClient->expects($this->exactly($this->httpClientCalls));
        } else {
            $mock = $this->httpClient->expects($this->once());
        }

        $mock->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($response, $contentType) {
                $request = $request->withAddedHeader('Content-Type', $contentType ?: $this->httpResponseContentType);

                return MessageFactoryDiscovery::find()
                    ->createResponse(
                        $this->httpResponseHttpCode,
                        null,
                        $request->getHeaders(),
                        $response ?: $this->httpResponse
                    )
                ;
            });
    }

    protected function createResourceOwner(string $name, array $options = [], array $paths = [])
    {
        $this->httpClient = $this->createMock(HttpClient::class);

        $this->storage = $this->createMock(RequestDataStorageInterface::class);

        /** @var HttpUtils $httpUtils */
        $httpUtils = $this->createMock(HttpUtils::class);

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

        if (!\in_array(ResourceOwnerInterface::class, class_implements($this->resourceOwnerClass), true)) {
            throw new \RuntimeException('Class is not implementing "ResourceOwnerInterface"!');
        }

        return new $this->resourceOwnerClass(new HttpMethodsClient($this->httpClient, new GuzzleMessageFactory()), $httpUtils, $options, $name, $this->storage);
    }
}
