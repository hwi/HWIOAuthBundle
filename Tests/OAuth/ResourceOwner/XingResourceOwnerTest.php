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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\XingResourceOwner;

class XingResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $resourceOwnerClass = XingResourceOwner::class;
    protected $userResponse = <<<json
{
    "users":[
        {
            "id":"42",
            "active_email":"foobar@example.com",
            "display_name":"foo bar",
            "first_name":"Foo",
            "last_name":"Bar",
            "photo_urls":{
                "large":"https:\/\/x2.xingassets.com\/img\/n\/nobody_m.140x185.jpg"
            }
        }
    ]
}
json;
    protected $paths = array(
        'identifier' => 'users.0.id',
        'nickname' => 'users.0.display_name',
        'firstname' => 'users.0.first_name',
        'lastname' => 'users.0.last_name',
        'realname' => array('users.0.first_name', 'users.0.last_name'),
        'profilepicture' => 'users.0.photo_urls.large',
        'email' => 'users.0.active_email',
    );

    public function testGetUserInformation()
    {
        $this->mockHttpClient($this->userResponse, 'application/json; charset=utf-8');

        $accessToken = array('oauth_token' => 'token', 'oauth_token_secret' => 'secret');
        $userResponse = $this->resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('42', $userResponse->getUsername());
        $this->assertEquals('foo bar', $userResponse->getNickname());
        $this->assertEquals('Foo', $userResponse->getFirstName());
        $this->assertEquals('Bar', $userResponse->getLastName());
        $this->assertEquals('Foo Bar', $userResponse->getRealName());
        $this->assertEquals('foobar@example.com', $userResponse->getEmail());
        $this->assertEquals('https://x2.xingassets.com/img/n/nobody_m.140x185.jpg', $userResponse->getProfilePicture());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
