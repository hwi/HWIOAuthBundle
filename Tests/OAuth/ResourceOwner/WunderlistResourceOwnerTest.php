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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\WunderlistResourceOwner;

class WunderlistResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = WunderlistResourceOwner::class;
    protected $userResponse = <<<json
{
    "data": {
        "id": 1,
        "name": "bar"
    }
}
json;

    protected $paths = [
        'identifier' => 'data.id',
        'nickname' => 'data.name',
        'realname' => 'data.name',
    ];

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid';
}
