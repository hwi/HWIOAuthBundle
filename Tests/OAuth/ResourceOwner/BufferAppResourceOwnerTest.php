<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\BufferAppResourceOwner;

class BufferAppResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = BufferAppResourceOwner::class;

    protected $userResponse = <<<json
{
    "id": "4f0c0a06512f7ef214000000"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'id',
        'realname' => 'id',
    );

    public function testGetUserInformation()
    {
        $this->mockHttpClient($this->userResponse, 'application/json; charset=utf-8');

        /**
         * @var \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertEquals('4f0c0a06512f7ef214000000', $userResponse->getUsername());
        $this->assertEquals('4f0c0a06512f7ef214000000', $userResponse->getNickname());
        $this->assertEquals('4f0c0a06512f7ef214000000', $userResponse->getRealName());
        $this->assertNull($userResponse->getEmail());
        $this->assertNull($userResponse->getProfilePicture());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
