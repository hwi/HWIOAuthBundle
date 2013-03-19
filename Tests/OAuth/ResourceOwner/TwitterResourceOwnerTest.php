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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitterResourceOwner;

class TwitterResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "screen_name": "bar"
}
json;
    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'screen_name',
        'realname'   => 'name',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        $options = array_merge(
            array(
                'authorization_url'   => 'https://api.twitter.com/oauth/authenticate',
                'request_token_url'   => 'https://api.twitter.com/oauth/request_token',
                'access_token_url'    => 'https://api.twitter.com/oauth/access_token',
                'infos_url'           => 'http://api.twitter.com/1/account/verify_credentials.json',
            ),
            $options
        );

        return new TwitterResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
