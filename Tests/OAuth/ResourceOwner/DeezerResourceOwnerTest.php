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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\DeezerResourceOwner;

/**
 * @author Kieu Anh Tuan <passkey1510@gmail.com>
 */
class DeezerResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = DeezerResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": 3038840,
    "name": "passkey",
    "lastname": "Tuan",
    "firstname": "Kieu",
    "birthday": "1984-10-15",
    "inscription_date": "2008-08-12",
    "gender": "M",
    "link": "http://www.deezer.com/profile/3038840",
    "picture": "https://api.deezer.com/user/3038840/image",
    "picture_small": "https://cdns-images.deezer.com/images/user/212f8886ec6b216b724aecbb994f8d13/56x56-000000-80-0-0.jpg",
    "picture_medium": "https://cdns-images.deezer.com/images/user/212f8886ec6b216b724aecbb994f8d13/250x250-000000-80-0-0.jpg",
    "picture_big": "https://cdns-images.deezer.com/images/user/212f8886ec6b216b724aecbb994f8d13/500x500-000000-80-0-0.jpg",
    "country": "FR",
    "lang": "fr",
    "tracklist": "https://api.deezer.com/user/3038840/flow",
    "type": "user",
    "status": 0
}
json;
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'name',
        'realname' => 'firstname',
        'email' => 'email',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'profilepicture' => 'picture',
        'gender' => 'gender',
    );

    public function testGetUserInformation()
    {
        $this->mockHttpClient($this->userResponse);

        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertEquals('passkey', $userResponse->getNickname());
        $this->assertEquals('Kieu', $userResponse->getRealName());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
