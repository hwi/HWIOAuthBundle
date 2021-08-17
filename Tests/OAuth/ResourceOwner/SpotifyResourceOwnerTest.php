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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SpotifyResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;

/**
 * @author Janne Savolainen <janne.savolainen@sempre.fi>
 */
class SpotifyResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected string $resourceOwnerClass = SpotifyResourceOwner::class;
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
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'id',
        'realname' => 'display_name',
        'email' => 'email',
    ];

    public function testGetUserInformation()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /**
         * @var AbstractUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertEquals('wizzler', $userResponse->getUsername());
        $this->assertEquals('wizzler', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /**
         * @var CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
