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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitterResourceOwner;

final class TwitterResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected string $resourceOwnerClass = TwitterResourceOwner::class;
    protected string $userResponse = <<<json
{
    "id_str": "1",
    "screen_name": "bar"
}
json;
    protected array $paths = [
        'identifier' => 'id_str',
        'nickname' => 'screen_name',
        'realname' => 'name',
    ];

    public function testGetUserInformationWithEmail(): void
    {
        $resourceOwner = $this->createResourceOwner(
            ['include_email' => true],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json; charset=utf-8'),
            ]
        );

        $accessToken = ['oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'user_id' => '1', 'screen_name' => 'bar'];

        $resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('http://user.info/?test=1&include_email=true', $resourceOwner->getOption('infos_url'));
    }

    public function testGetUserInformation(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json; charset=utf-8'),
            ]
        );

        $accessToken = ['oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'user_id' => '1', 'screen_name' => 'bar'];
        $userResponse = $resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertEquals($accessToken['oauth_token_secret'], $userResponse->getTokenSecret());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
