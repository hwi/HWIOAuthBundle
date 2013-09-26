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

class DropboxResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = '{"uid": "1", "email": "bar"}';
    protected $paths = array(
        'identifier' => 'uid',
        'nickname'   => 'email',
        'realname'   => 'display_name',
        'email'      => 'email',
    );

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new DropboxResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
