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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\StereomoodResourceOwner;
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth1ResourceOwnerTestCase;

final class StereomoodResourceOwnerTest extends GenericOAuth1ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = StereomoodResourceOwner::class;
    protected string $userResponse = <<<json
{
    "oauth_token": "token"
}
json;
    protected array $paths = [
        'identifier' => 'oauth_token',
        'nickname' => 'oauth_token',
    ];

    public function testGetUserInformation(): void
    {
        $accessToken = [
            'oauth_token' => 'token',
            'oauth_token_secret' => 'secret',
        ];

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );
        $userResponse = $resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('token', $userResponse->getUserIdentifier());
        $this->assertEquals('token', $userResponse->getNickname());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertEquals($accessToken['oauth_token_secret'], $userResponse->getTokenSecret());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass(): void
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner(['user_response_class' => $class]);

        $accessToken = [
            'oauth_token' => 'token',
            'oauth_token_secret' => 'secret',
        ];

        $userResponse = $resourceOwner->getUserInformation($accessToken);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUserIdentifier());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
    }
}
