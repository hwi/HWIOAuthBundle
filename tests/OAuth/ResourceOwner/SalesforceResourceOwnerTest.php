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
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SalesforceResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class SalesforceResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = SalesforceResourceOwner::class;
    protected string $userResponse = <<<json
{
    "user_id": "1",
    "nick_name": "bar",
    "email": "baz",
    "photos": {
        "picture": "url"
    }
}
json;

    protected array $paths = [
        'identifier' => 'user_id',
        'nickname' => 'nick_name',
        'realname' => 'nick_name',
        'email' => 'email',
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

        /**
         * @var AbstractUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(
            ['access_token' => 'token', 'id' => 'https://login.salesforce.com/services/oauth2/someuser']
        );

        $this->assertEquals('1', $userResponse->getUserIdentifier());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertEquals('url', $userResponse->getProfilePicture());
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
                $this->createMockResponse('invalid', 'application/json; charset=utf-8', 401),
            ]
        );
        $resourceOwner->getUserInformation(
            ['access_token' => 'token', 'id' => 'https://login.salesforce.com/services/oauth2/someuser']
        );
    }

    public function testCustomResponseClass(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
