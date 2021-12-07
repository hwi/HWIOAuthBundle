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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\YahooResourceOwner;
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class YahooResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = YahooResourceOwner::class;
    protected string $userResponse = <<<json
{
    "sub": "1",
    "given_name": "bar"
}
json;
    protected array $paths = [
        'identifier' => 'sub',
        'nickname' => 'given_name',
        'realname' => 'name',
        'email' => 'email',
        'firstname' => 'given_name',
        'lastname' => 'family_name',
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

        $accessToken = ['access_token' => 'token', 'oauth_token_secret' => 'secret'];
        $userResponse = $resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals($accessToken['access_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
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
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token', 'oauth_token_secret' => 'secret']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }
}
