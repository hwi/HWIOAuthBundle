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

use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SinaWeiboResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;

final class SinaWeiboResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = SinaWeiboResourceOwner::class;
    protected string $userResponse = <<<json
{
    "id": "1",
    "screen_name": "bar",
    "profile_image_url": "http://tp1.sinaimg.cn/1404376560/50/0/1"
}
json;
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'screen_name',
        'realname' => 'screen_name',
        'profilepicture' => 'profile_image_url',
    ];

    public function testGetUserInformation(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /**
         * @var AbstractUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token', 'uid' => '1']);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationFailure(): void
    {
        $this->expectException(HttpTransportException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('invalid'),
            ]
        );
        $resourceOwner->getUserInformation(['access_token' => 'token', 'uid' => 'someuser']);
    }

    public function testCustomResponseClass(): void
    {
        $class = CustomUserResponse::class;

        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /**
         * @var CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token', 'uid' => '1']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
