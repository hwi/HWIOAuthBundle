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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\BufferAppResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class BufferAppResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = BufferAppResourceOwner::class;

    protected $userResponse = <<<json
{
    "id": "4f0c0a06512f7ef214000000"
}
json;

    protected $paths = [
        'identifier' => 'id',
        'nickname' => 'id',
        'realname' => 'id',
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

        /**
         * @var AbstractUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertEquals('4f0c0a06512f7ef214000000', $userResponse->getUsername());
        $this->assertEquals('4f0c0a06512f7ef214000000', $userResponse->getNickname());
        $this->assertEquals('4f0c0a06512f7ef214000000', $userResponse->getRealName());
        $this->assertNull($userResponse->getEmail());
        $this->assertNull($userResponse->getProfilePicture());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetUserInformationFailure()
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
