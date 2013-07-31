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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\DeviantartResourceOwner;

class DeviantartResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "username": "kouiskas",
    "symbol": "$",
    "usericonurl": "http://a.deviantart.net/avatars/k/o/kouiskas.png?15"
}
json;
    protected $paths = array(
        'identifier'     => 'username',
        'nickname'       => 'username',
        'profilepicture' => 'usericonurl',
    );

    public function testGetUserInformation()
    {
        $this->mockBuzz($this->userResponse, 'application/json; charset=utf-8');

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertEquals('kouiskas', $userResponse->getUsername());
        $this->assertEquals('kouiskas', $userResponse->getNickname());
        $this->assertNull($userResponse->getRealName());
        $this->assertEquals('http://a.deviantart.net/avatars/k/o/kouiskas.png?15', $userResponse->getProfilePicture());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'   => 'https://www.deviantart.com/oauth2/draft15/authorize',
                'access_token_url'    => 'https://www.deviantart.com/oauth2/draft15/token',
                'infos_url'           => 'https://www.deviantart.com/api/draft15/user/whoami',
            ),
            $options
        );

        return new DeviantartResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
