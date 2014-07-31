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

class WeixinResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    /**
     * {@inheritDoc}
     */
    protected $userResponse = <<<json
{
    "openid": "123",
    "nickname": "bar",
}
json;

    /**
     * {@inheritDoc}
     */
    protected $paths = array(
        'identifier'     => 'openid',
        'nickname'       => 'nickname',
        'realname'       => 'nickname',
        'profilepicture' => 'headimgurl',
    );

    /**
     * {@inheritDoc}
     */
    public function testGetUserInformation()
    {
        $this->mockBuzz($this->userResponse, 'application/json; charset=utf-8');

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertEquals('1234', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
