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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\StravaResourceOwner;

/**
 * @author Artem Genvald <genvaldartem@gmail.com>
 */
final class StravaResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = StravaResourceOwner::class;
    /**
     * {@inheritdoc}
     */
    protected string $userResponse = <<<json
{
    "id": "1",
    "firstname": "Foo",
    "lastname": "Bar",
    "profile_medium": "http://www.gravatar.com/avatar/default",
    "email": "foo@acme.com"
}
json;

    /**
     * {@inheritdoc}
     */
    protected array $paths = [
        'identifier' => 'id',
        'realname' => ['firstname', 'lastname'],
        'profilepicture' => 'profile_medium',
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

        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('Foo Bar', $userResponse->getRealName());
        $this->assertEquals('http://www.gravatar.com/avatar/default', $userResponse->getProfilePicture());
        $this->assertEquals('foo@acme.com', $userResponse->getEmail());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
