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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\DiscogsResourceOwner;

class DiscogsResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected $resourceOwnerClass = DiscogsResourceOwner::class;
    protected $userResponse = <<<json
{
  "id": 1,
  "username": "bar",
  "resource_url": "http://api.discogs.com/users/bar",
  "consumer_name": "Your Application Name"
}
json;
    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'username',
    );
}
