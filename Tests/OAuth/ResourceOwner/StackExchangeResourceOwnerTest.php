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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\StackExchangeResourceOwner;

class StackExchangeResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "user_id": "1",
    "display_name": "bar"
}
json;

    protected $paths = array(
        'identifier'  => 'user_id',
        'nickname'    => 'display_name',
        'realname'    => 'display_name'
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                 'authorization_url'   => 'https://stackexchange.com/oauth',
                 'access_token_url'    => 'https://stackexchange.com/oauth/access_token',
                 'infos_url'           => 'https://api.stackexchange.com/2.0/me',
                 'scope'               => 'no_expiry',
            ),
            $options
        );

        return new StackExchangeResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
