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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\LinkedinOpenIdResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class LinkedinOpenIdResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = LinkedinOpenIdResourceOwner::class;
    protected string $userResponse = <<<json
{
    "sub": "CM9X5BxxK8",
    "email_verified": true,
    "name": "John Smith",
    "locale": {
      "country": "US",
      "language": "en"
    },
    "given_name": "John",
    "family_name": "Smith",
    "email": "example@website.com",
    "picture": "https://website.com/picture.jpg"
}
json;
    protected array $paths = [
        'identifier' => 'sub',
        'nickname' => 'email',
        'firstname' => 'given_name',
        'lastname' => 'family_name',
        'email' => 'email',
        'profilepicture' => 'picture',
    ];
    protected bool $csrf = true;

    protected string $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=openid+profile+email';

    protected int $httpClientCalls = 1;

    public function testCustomResponseClass(): void
    {
        $class = CustomUserResponse::class;

        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json; charset=utf-8'),
                $this->createMockResponse($this->userResponse, 'application/json; charset=utf-8'),
            ]
        );

        /** @var CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUserIdentifier());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformation(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json; charset=utf-8'),
                $this->createMockResponse($this->userResponse, 'application/json; charset=utf-8'),
            ]
        );

        /** @var AbstractUserResponse $userResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertEquals('CM9X5BxxK8', $userResponse->getUserIdentifier());
        $this->assertEquals('example@website.com', $userResponse->getNickname());
        $this->assertEquals('John', $userResponse->getFirstName());
        $this->assertEquals('Smith', $userResponse->getLastName());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
