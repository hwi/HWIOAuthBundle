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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TrelloResourceOwner;

class TrelloResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "_id": "1",
    "username": "bar",
    "fullName": "foo"
}
json;
    protected $paths = array(
        'identifier'     => '_id',
        'nickname'       => 'username',
        'realname'       => 'fullName',
        'email'          => 'email',
        'profilepicture' => 'avatarSource',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new TrelloResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
