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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitchResourceOwner;

class TwitchResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "_id": "1",
    "display_name": "bar",
    "name": "bar",
    "email": "foobar@example.com",
    "logo": "example.com/logo.png"
}
json;

    protected $paths = array(
        'identifier'     => '_id',
        'nickname'       => 'display_name',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'logo',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'       => 'https://api.twitch.tv/kraken/oauth2/authorize',
                'access_token_url'        => 'https://api.twitch.tv/kraken/oauth2/token',
                'infos_url'               => 'https://api.twitch.tv/kraken/user',
                'scope'                   => 'user_read'
            ),
            $options
        );

        return new TwitchResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
