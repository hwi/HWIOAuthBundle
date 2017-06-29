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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\BitlyResourceOwner;

class BitlyResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = BitlyResourceOwner::class;
    protected $userResponse = <<<json
{
    "data": {
        "display_name": "bar",
        "full_name": "foo",
        "login": "1",
        "profile_url": "http://bitly.com/u/bitlyapioauthdemo"
    }
}
json;
    protected $paths = array(
        'identifier' => 'data.login',
        'nickname' => 'data.display_name',
        'realname' => 'data.full_name',
        'profilepicture' => 'data.profile_image',
    );
}
