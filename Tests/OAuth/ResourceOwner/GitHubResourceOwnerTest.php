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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GitHubResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;

class GitHubResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = GitHubResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "login": "bar"
}
json;

    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'login',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'avatar_url',
    ];

    protected int $httpClientCalls = 1;

    public function testRevokeToken()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json', 204),
            ]
        );

        $this->assertTrue($resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"id": "666"}', 'application/json', 404),
            ]
        );

        $this->assertFalse($resourceOwner->revokeToken('token'));
    }

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;

        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse),
                $this->createMockResponse($this->userResponse),
            ]
        );

        /** @var CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformation()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
                $this->createMockResponse($this->userResponse),
            ]
        );

        /** @var AbstractUserResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
