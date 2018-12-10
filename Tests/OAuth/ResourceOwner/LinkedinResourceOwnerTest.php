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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\LinkedinResourceOwner;

class LinkedinResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = LinkedinResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "formattedName": "bar"
}
json;
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'formattedName',
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'realname' => 'formattedName',
        'email' => 'emailAddress',
        'profilepicture' => 'pictureUrl',
    );
    protected $csrf = true;
}
