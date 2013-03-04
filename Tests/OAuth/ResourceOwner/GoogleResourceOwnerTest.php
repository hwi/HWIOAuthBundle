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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;

class GoogleResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "name": "bar"
}
json;

    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'name',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'picture',
    );

    protected $options = array(
        'client_id'           => 'clientid',
        'client_secret'       => 'clientsecret',

        'infos_url'           => 'http://user.info/',
        'authorization_url'   => 'http://user.auth/',
        'access_token_url'    => 'http://user.access/',

        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',

        'scope'               => '',
        'request_visible_actions' => '',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                 'authorization_url'   => 'https://accounts.google.com/o/oauth2/auth',
                 'access_token_url'    => 'https://accounts.google.com/o/oauth2/token',
                 'infos_url'           => 'https://www.googleapis.com/oauth2/v1/userinfo',
                 'scope'               => 'userinfo.profile',
            ),
            $options
        );

        return new GoogleResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }

    public function testRequestVisibleActions()
    {
        $resourceOwner = $this->createResourceOwner('google', ['request_visible_actions' => 'http://schemas.google.com/AddActivity']);
        $this->assertEquals(
            $this->options['authorization_url'].'?request_visible_actions=http%3A%2F%2Fschemas.google.com%2FAddActivity&response_type=code&client_id=clientid&scope=&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }
}
