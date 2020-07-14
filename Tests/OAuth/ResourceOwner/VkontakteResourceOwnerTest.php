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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\VkontakteResourceOwner;

class VkontakteResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = VkontakteResourceOwner::class;
    protected $userResponse = <<<json
{
    "response": {
        "user_id": "1",
        "user_name": "bar"
    }
}
json;

    protected $paths = [
        'identifier' => 'response.user_id',
        'nickname' => 'response.user_name',
        'realname' => 'response.user_name',
    ];

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=email';
}
