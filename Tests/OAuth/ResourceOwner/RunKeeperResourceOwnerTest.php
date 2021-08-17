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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\RunKeeperResourceOwner;

/**
 * @author Artem Genvald <genvaldartem@gmail.com>
 */
class RunKeeperResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = RunKeeperResourceOwner::class;
    /**
     * {@inheritdoc}
     */
    protected $userResponse = <<<json
{
    "name": "Foo Bar",
    "medium_picture": "http://www.gravatar.com/avatar/default"
}
json;

    /**
     * {@inheritdoc}
     */
    protected $paths = [
        'realname' => 'name',
        'profilepicture' => 'medium_picture',
    ];

    public function testGetUserInformation()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json; charset=utf-8'),
            ]
        );

        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertEquals('Foo Bar', $userResponse->getRealName());
        $this->assertEquals('http://www.gravatar.com/avatar/default', $userResponse->getProfilePicture());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
