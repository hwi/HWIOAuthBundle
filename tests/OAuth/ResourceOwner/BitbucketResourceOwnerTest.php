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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\BitbucketResourceOwner;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth1ResourceOwnerTestCase;

final class BitbucketResourceOwnerTest extends GenericOAuth1ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = BitbucketResourceOwner::class;
    protected string $userResponse = <<<json
{
    "user": {
        "username": "1",
        "display_name": "bar"
    }
}
json;
    protected array $paths = [
        'identifier' => 'user.username',
        'nickname' => 'user.username',
        'realname' => 'user.display_name',
    ];

    public function testGetUserInformation(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json; charset=utf-8'),
            ]
        );

        $accessToken = ['oauth_token' => 'token', 'oauth_token_secret' => 'secret'];
        $userResponse = $resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('1', $userResponse->getUserIdentifier());
        $this->assertEquals('1', $userResponse->getNickname());
        $this->assertEquals('bar', $userResponse->getRealName());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
