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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;

/**
 * @author Janne Savolainen <janne.savolainen@sempre.fi>
 */
class SpotifyResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
  "birthdate": "1937-06-01",
  "country": "SE",
  "display_name": "JM Wizzler",
  "email": "email@example.com",
  "external_urls": {
    "spotify": "https://open.spotify.com/user/wizzler"
  },
  "followers" : {
    "href" : null,
    "total" : 3829
  },
  "href": "https://api.spotify.com/v1/users/wizzler",
  "id": "wizzler",
  "images": [
    {
      "height": null,
      "url": "https://fbcdn-profile-a.akamaihd.net/hprofile-ak-frc3/t1.0-1/1970403_10152215092574354_1798272330_n.jpg",
      "width": null
    }
  ],
  "product": "premium",
  "type": "user", 
  "uri": "spotify:user:wizzler"
}
json;
    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'id',
        'realname'       => 'display_name',
        'email'          => 'email'
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new SpotifyResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }

    public function testGetUserInformation()
    {
        $this->mockBuzz($this->userResponse);

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertEquals('wizzler', $userResponse->getUsername());
        $this->assertEquals('wizzler', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner('oauth2', array('user_response_class' => $class));

        $this->mockBuzz();

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(array('access_token' => 'token')); 

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
