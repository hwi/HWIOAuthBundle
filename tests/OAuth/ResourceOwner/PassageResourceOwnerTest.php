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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\PassageResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class PassageResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = PassageResourceOwner::class;

    protected array $options = [
        'client_id' => 'clientid',
        'client_secret' => 'clientsecret',
        'sub_domain' => 'subdomain',
    ];

    protected string $userResponse = <<<json
{
    "sub": "cIouEYQZIxZkz69XlAGvQDeN",
    "email": "foo@example.com",
    "email_verified": true,
    "phone_number_verified": false
}
json;

    protected array $paths = [
        'identifier' => 'sub',
        'email' => 'email',
        'phone_number' => 'phone_number',
        'email_verified' => 'email_verified',
        'phone_number_verified' => 'phone_number_verified',
    ];

    protected string $authorizationUrlBasePart = 'https://subdomain.withpassage.com/authorize?response_type=code&client_id=clientid&scope=openid+email';

    public function testGetUserInformation(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /** @var AbstractUserResponse $userResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertSame('cIouEYQZIxZkz69XlAGvQDeN', $userResponse->getUsername());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testRevokeToken(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json'),
            ]
        );

        $this->assertTrue($resourceOwner->revokeToken('token'));
    }
}
