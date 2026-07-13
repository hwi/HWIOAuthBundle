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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\DiscordResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class DiscordResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = DiscordResourceOwner::class;
    protected string $userResponse = <<<json
{
    "id": "80351110224678912",
    "username": "nelly",
    "global_name": "Nelly",
    "email": "nelly@example.com",
    "avatar": "8342729096ea3675442027381ff50dfe"
}
json;

    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'global_name',
        'email' => 'email',
        'profilepicture' => 'avatar_url',
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

        /** @var AbstractUserResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertEquals('80351110224678912', $userResponse->getUserIdentifier());
        $this->assertEquals('nelly', $userResponse->getNickname());
        $this->assertEquals('Nelly', $userResponse->getRealName());
        $this->assertEquals('nelly@example.com', $userResponse->getEmail());
        $this->assertEquals(
            'https://cdn.discordapp.com/avatars/80351110224678912/8342729096ea3675442027381ff50dfe.png',
            $userResponse->getProfilePicture()
        );
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationWithoutAvatar(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"id": "80351110224678912", "username": "nelly", "avatar": null}'),
            ]
        );

        /** @var AbstractUserResponse */
        $userResponse = $resourceOwner->getUserInformation($this->tokenData);

        $this->assertEquals('80351110224678912', $userResponse->getUserIdentifier());
        $this->assertEquals('nelly', $userResponse->getNickname());
        $this->assertNull($userResponse->getProfilePicture());
    }
}
