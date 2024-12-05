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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AmazonCognitoResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class AmazonCognitoResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = AmazonCognitoResourceOwner::class;
    protected string $userResponse = <<<json
{
    "sub": "111",
    "name": "bar",
    "email": "baz@example.com"
}
json;

    protected array $paths = [
        'identifier' => 'user_id',
        'nickname' => 'name',
        'realname' => 'name',
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
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertEquals('111', $userResponse->getUserIdentifier());
        $this->assertEquals('baz@example.com', $userResponse->getEmail());
        $this->assertEquals('bar', $userResponse->getRealName());
        $this->assertNull($userResponse->getFirstName());
        $this->assertNull($userResponse->getProfilePicture());
        $this->assertEquals('token', $userResponse->getAccessToken());
    }

    public function testGetUserInformationFailure(): void
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('invalid', 'application/json; charset=utf-8', 401),
            ]
        );

        $resourceOwner->getUserInformation($this->tokenData);
    }
}
