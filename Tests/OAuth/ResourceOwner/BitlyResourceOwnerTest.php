<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\OAuth\ResourceOwner;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\BitlyResourceOwner;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class BitlyResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = BitlyResourceOwner::class;
    protected string $userResponse = <<<json
{
    "data": {
        "display_name": "bar",
        "full_name": "foo",
        "login": "1",
        "profile_url": "http://bitly.com/u/bitlyapioauthdemo"
    }
}
json;
    protected array $paths = [
        'identifier' => 'data.login',
        'nickname' => 'data.display_name',
        'realname' => 'data.full_name',
        'profilepicture' => 'data.profile_image',
    ];
}
