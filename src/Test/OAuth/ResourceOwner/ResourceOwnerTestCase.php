<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Test\Fixtures\ResourceOwner\OAuth1ResourceOwnerStub;
use HWI\Bundle\OAuthBundle\Test\Fixtures\ResourceOwner\OAuth2ResourceOwnerStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Security\Http\HttpUtils;

abstract class ResourceOwnerTestCase extends TestCase
{
    protected ?MockHttpClient $httpClient = null;

    /** @var MockObject&RequestDataStorageInterface */
    protected $storage;

    protected string $state = 'eyJzdGF0ZSI6InJhbmRvbSJ9';
    protected bool $csrf = false;

    protected int $httpResponseHttpCode = 200;
    protected int $httpClientCalls = 0;

    protected array $options = [];
    /** @var array<string, mixed> */
    protected array $paths = [];

    /** @var class-string */
    protected string $resourceOwnerClass;

    protected function createMockResponse(?string $response, string $contentType = null, int $httpCode = null): MockResponse
    {
        return new MockResponse(
            $response ?: '',
            [
                'http_code' => $httpCode ?: 200,
                'response_headers' => [
                    'Content-Type' => $contentType ?: 'application/json',
                ],
            ]
        );
    }

    protected function prepareResourceOwnerName(): string
    {
        return str_replace(['generic', 'resourceownertest'], '', strtolower(__CLASS__));
    }

    /**
     * @return OAuth1ResourceOwnerStub|OAuth2ResourceOwnerStub
     */
    protected function createResourceOwner(array $options = [], array $paths = [], array $responses = []): ResourceOwnerInterface
    {
        $this->storage = $this->createMock(RequestDataStorageInterface::class);

        /** @var HttpUtils $httpUtils */
        $httpUtils = $this->createMock(HttpUtils::class);

        $resourceOwner = $this->setUpResourceOwner(
            $this->prepareResourceOwnerName(),
            $httpUtils,
            array_merge($this->options, $options),
            $responses
        );
        $resourceOwner->addPaths(array_merge($this->paths, $paths));

        return $resourceOwner;
    }

    /**
     * @return OAuth1ResourceOwnerStub|OAuth2ResourceOwnerStub
     */
    protected function setUpResourceOwner(string $name, HttpUtils $httpUtils, array $options, array $responses): ResourceOwnerInterface
    {
        if (!$this->resourceOwnerClass) {
            throw new \RuntimeException('Missing resource owner class declaration!');
        }

        if (!\in_array(ResourceOwnerInterface::class, class_implements($this->resourceOwnerClass), true)) {
            throw new \RuntimeException('Class is not implementing "ResourceOwnerInterface"!');
        }

        $resourceOwnerClass = $this->resourceOwnerClass;
        if (GenericOAuth1ResourceOwner::class === $resourceOwnerClass) {
            $resourceOwnerClass = OAuth1ResourceOwnerStub::class;
        } elseif (GenericOAuth2ResourceOwner::class === $resourceOwnerClass) {
            $resourceOwnerClass = OAuth2ResourceOwnerStub::class;
        }

        return new $resourceOwnerClass(
            $this->httpClient ?: new MockHttpClient($responses),
            $httpUtils,
            $options,
            $name,
            $this->storage
        );
    }
}
