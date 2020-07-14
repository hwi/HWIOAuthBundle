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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GeniusResourceOwner;

class GeniusResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = GeniusResourceOwner::class;

    protected $userResponse = <<<json
{
    "meta":{
        "status":200
    },
    "response":{
        "user":{
            "avatar":{
                "tiny":{
                    "url":"https://images.rapgenius.com/avatars/tiny/1",
                    "bounding_box":{
                        "width":16,
                        "height":16
                    }
                },
                "thumb":{
                    "url":"https://images.rapgenius.com/avatars/thumb/1",
                    "bounding_box":{
                        "width":32,
                        "height":32
                    }
                },
                "small":{
                    "url":"https://images.rapgenius.com/avatars/small/1",
                    "bounding_box":{
                        "width":100,
                        "height":100
                    }
                },
                "medium":{
                    "url":"https://images.rapgenius.com/avatars/medium/1",
                    "bounding_box":{
                        "width":300,
                        "height":400
                    }
                }
            },
            "email":"bar@domain.com",
            "login":"bar",
            "name":"bar",
            "id":1
        }
    }
}
json;

    protected $paths = [
        'identifier' => 'response.user.id',
        'nickname' => 'response.user.name',
        'realname' => 'response.user.name',
        'email' => 'response.user.email',
        'profilepicture' => 'response.user.avatar.medium.url',
    ];

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=me';
}
