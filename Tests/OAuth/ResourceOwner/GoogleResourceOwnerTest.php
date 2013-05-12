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

    public function testGetOptionAccessType()
    {
        $this->assertEquals('offline', $this->resourceOwner->getOption('access_type'));
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&redirect_uri=http%3A%2F%2Fredirect.to%2F&access_type=offline',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url' => 'https://accounts.google.com/o/oauth2/auth',
                'access_token_url'  => 'https://accounts.google.com/o/oauth2/token',
                'infos_url'         => 'https://www.googleapis.com/oauth2/v1/userinfo',
                'scope'             => 'https://www.googleapis.com/auth/userinfo.profile',

                'access_type'       => 'offline'
            ),
            $options
        );

        return new GoogleResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
