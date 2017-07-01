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
use Symfony\Component\Security\Http\HttpUtils;

class Auth0ResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = Auth0ResourceOwner::class;
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
        'identifier' => 'user_id',
        'nickname' => 'nickname',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'picture',
    );

    protected $expectedUrls = array(
        'authorization_url' => 'https://example.oauth0.com/authorize?auth0Client=eyJuYW1lIjoiSFdJT0F1dGhCdW5kbGUiLCJ2ZXJzaW9uIjoidW5rbm93biIsImVudmlyb25tZW50Ijp7Im5hbWUiOiJQSFAiLCJ2ZXJzaW9uIjoiRkFLRV9QSFBfVkVSU0lPTl9GT1JfVEVTVFMifX0=&response_type=code&client_id=clientid&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'https://example.oauth0.com/authorize?auth0Client=eyJuYW1lIjoiSFdJT0F1dGhCdW5kbGUiLCJ2ZXJzaW9uIjoidW5rbm93biIsImVudmlyb25tZW50Ijp7Im5hbWUiOiJQSFAiLCJ2ZXJzaW9uIjoiRkFLRV9QSFBfVkVSU0lPTl9GT1JfVEVTVFMifX0=&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
    );

    protected function setUpResourceOwner($name, HttpUtils $httpUtils, array $options)
    {
        $auth0Client = base64_encode(json_encode(array(
            'name' => 'HWIOAuthBundle',
            'version' => 'unknown',
            'environment' => array(
                'name' => 'PHP',
                'version' => 'FAKE_PHP_VERSION_FOR_TESTS',
            ),
        )));

        $options = array_merge(
            $options,
            array(
                'authorization_url' => '{base_url}/authorize?auth0Client='.$auth0Client,
                'access_token_url' => '{base_url}/oauth/token',
                'infos_url' => '{base_url}/userinfo',
                'auth0_client' => $auth0Client,
                'base_url' => 'https://example.oauth0.com',
            )
        );

        return parent::setUpResourceOwner($name, $httpUtils, $options);
    }
}
