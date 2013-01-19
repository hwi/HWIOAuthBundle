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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\WindowsLiveResourceOwner;

class WindowsLiveResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "name": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'name',
        'realname'   => 'name',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                 'authorization_url'   => 'https://login.live.com/oauth20_authorize.srf',
                 'access_token_url'    => 'https://login.live.com/oauth20_token.srf',
                 'infos_url'           => 'https://apis.live.net/v5.0/me',
            ),
            $options
        );

        return new WindowsLiveResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
