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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FacebookResourceOwner;

class FacebookResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "username": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'username',
        'realname'   => 'name',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                 'authorization_url'   => 'https://www.facebook.com/dialog/oauth',
                 'access_token_url'    => 'https://graph.facebook.com/oauth/access_token',
                 'infos_url'           => 'https://graph.facebook.com/me',
            ),
            $options
        );

        return new FacebookResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
