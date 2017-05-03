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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\WordpressResourceOwner;

class WordpressResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = WordpressResourceOwner::class;
    protected $userResponse = <<<json
{
    "ID": "1",
    "username": "bar",
    "display_name": "foo",
    "email": "sean@box.com",
    "avatar_URL": "https://www.box.com/api/avatar/large/17738362"
}
json;

    protected $paths = array(
        'identifier' => 'ID',
        'nickname' => 'username',
        'realname' => 'display_name',
        'email' => 'email',
        'profilepicture' => 'avatar_URL',
    );
}
