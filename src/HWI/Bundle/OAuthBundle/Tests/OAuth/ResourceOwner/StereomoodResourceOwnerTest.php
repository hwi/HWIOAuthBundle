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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\StereomoodResourceOwner;

class StereomoodResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "oauth_token": "token"
}
json;
    protected $paths = array(
        'identifier' => 'oauth_token',
        'nickname'   => 'oauth_token'
    );

    public function testGetUserInformation()
    {
        $accessToken = array(
            'oauth_token'        => 'token',
            'oauth_token_secret' => 'secret'
        );

        $userResponse = $this->resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('token', $userResponse->getUsername());
        $this->assertEquals('token', $userResponse->getNickname());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertEquals($accessToken['oauth_token_secret'], $userResponse->getTokenSecret());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner('oauth1', array('user_response_class' => $class));

        $accessToken = array(
            'oauth_token'        => 'token',
            'oauth_token_secret' => 'secret'
        );

        $userResponse = $resourceOwner->getUserInformation($accessToken);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new StereomoodResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
