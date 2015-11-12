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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\BoxResourceOwner;

class BoxResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "name": "bar",
    "login": "sean@box.com",
    "avatar_url": "https://www.box.com/api/avatar/large/17738362"
}
json;

    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'name',
        'realname'       => 'name',
        'email'          => 'login',
        'profilepicture' => 'avatar_url'
    );

    public function testRevokeToken()
    {
        $this->buzzResponseHttpCode = 200;
        $this->mockBuzz('{"access_token": "bar"}', 'application/json');

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $this->buzzResponseHttpCode = 401;
        $this->mockBuzz('{"access_token": "bar"}', 'application/json');

        $this->assertFalse($this->resourceOwner->revokeToken('token'));
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new BoxResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
