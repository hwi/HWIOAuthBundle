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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TrelloResourceOwner;

class TrelloResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $resourceOwnerClass = TrelloResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "username": "bar",
    "fullName": "foo"
}
json;
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'fullName',
        'email' => 'email',
        'profilepicture' => 'avatarSource',
    );

    public function testGetAuthorizationUrlContainOAuthTokenAndSecret()
    {
        $this->mockHttpClient('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json; charset=utf-8');

        $this->storage->expects($this->once())
            ->method('save')
            ->with($this->resourceOwner, array('oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'timestamp' => time()));

        $this->assertEquals(
            'http://user.auth/?test=3&scope=read&oauth_token=token',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }
}
