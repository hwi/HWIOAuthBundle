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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SoundcloudResourceOwner;

class SoundcloudResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = SoundcloudResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "username": "bar",
    "full_name": "baz"
}
json;

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=non-expiring';

    protected $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'full_name',
    ];
}
