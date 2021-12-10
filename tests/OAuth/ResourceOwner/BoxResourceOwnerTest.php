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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\BoxResourceOwner;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class BoxResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = BoxResourceOwner::class;
    protected string $userResponse = <<<json
{
    "id": "1",
    "name": "bar",
    "login": "sean@box.com",
    "avatar_url": "https://www.box.com/api/avatar/large/17738362"
}
json;

    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'name',
        'realname' => 'name',
        'email' => 'login',
        'profilepicture' => 'avatar_url',
    ];

    public function testRevokeToken(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"access_token": "bar"}', 'application/json'),
            ]
        );

        $this->assertTrue($resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"access_token": "bar"}', 'application/json', 401),
            ]
        );

        $this->assertFalse($resourceOwner->revokeToken('token'));
    }
}
