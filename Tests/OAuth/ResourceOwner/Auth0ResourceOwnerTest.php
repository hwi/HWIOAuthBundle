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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\Auth0ResourceOwner;

class Auth0ResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
  "email": "baz",
  "email_verified": false,
  "clientID": "yyy",
  "picture": "https://secure.gravatar.com/avatar/xxx.png",
  "user_id": "1",
  "name": "bar",
  "nickname": "bar",
  "identities": [
    {
      "user_id": "xxx",
      "provider": "auth0",
      "connection": "Username-Password-Authentication",
      "isSocial": false
    }
  ]
}
json;

    protected $paths = array(
        'identifier'     => 'user_id',
        'nickname'       => 'nickname',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'picture',
    );

    protected $expectedUrls = array(
        'authorization_url'      => 'http://user.auth/?test=2&response_type=code&client_id=clientid&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'base_url' => 'https://example.oauth0.com'
            ),
            $options
        );

        return new Auth0ResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
