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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\DropboxResourceOwner;

class DropboxResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $userResponse = '{"uid": "1", "email": "bar"}';
    protected $paths        = array(
        'identifier' => 'uid',
        'nickname'   => 'email',
        'realname'   => 'displayName',
    );

    public function testGetAuthorizationUrlContainOAuthTokenAndSecret()
    {
        $this->mockBuzz('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json; charset=utf-8');

        $this->storage->expects($this->once())
            ->method('save')
            ->with($this->resourceOwner, array('oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'timestamp' => time()));

        $this->assertEquals(
            $this->options['authorization_url'].'&oauth_token=token&oauth_callback=http%3A%2F%2Fredirect.to%2F',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'   => 'https://api.dropbox.com/1/oauth/authorize',
                'request_token_url'   => 'https://api.dropbox.com/1/oauth/request_token',
                'access_token_url'    => 'https://api.dropbox.com/1/oauth/access_token',
                'infos_url'           => 'https://api.dropbox.com/1/account/info',

                'signature_method'    => 'PLAINTEXT'
            ),
            $options
        );

        return new DropboxResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
