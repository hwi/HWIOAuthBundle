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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AmazonResourceOwner;

class AmazonResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "user_id": "1",
    "name": "bar",
    "email": "baz"
}
json;

    protected $paths = array(
        'identifier' => 'user_id',
        'nickname'   => 'name',
        'realname'   => 'name',
        'email'      => 'email',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                 'authorization_url' => 'https://www.amazon.com/ap/oa',
                 'access_token_url'  => 'https://www.amazon.com/auth/o2/token',
                 'infos_url'         => 'https://api.amazon.com/user/profile',

                 'scope'             => 'profile',
            ),
            $options
        );

        return new AmazonResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
