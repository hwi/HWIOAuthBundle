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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\KeycloakResourceOwner;

class KeycloakResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $options = [
      'client_id' => 'clientid',
      'client_secret' => 'clientsecret',
      'realms' => 'example',

      'infos_url' => 'http://keycloak.info/auth',
      'authorization_url' => 'http://keycloak.auth/auth',
      'access_token_url' => 'http://keycloak.auth/auth',

      'attr_name' => 'access_token',
    ];

    protected $expectedUrls = [
      'authorization_url' => 'http://keycloak.auth/auth/realms/example/protocol/openid-connect/auth?response_type=code&client_id=clientid&scope=name%2Cemail&redirect_uri=http%3A%2F%2Fredirect.to%2F&approval_prompt=auto',
      'authorization_url_csrf' => 'http://keycloak.auth/auth/realms/example/protocol/openid-connect/auth?response_type=code&client_id=clientid&scope=name%2Cemail&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&approval_prompt=auto',
    ];

    protected $resourceOwnerClass = KeycloakResourceOwner::class;
}
