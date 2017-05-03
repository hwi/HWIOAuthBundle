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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\AsanaResourceOwner;

class AsanaResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = AsanaResourceOwner::class;
    protected $userResponse = <<<json
{
    "data": {
        "id": "1",
        "name": "bar",
        "email": "foo@bar.baz"
    }
}
json;

    protected $paths = array(
        'identifier' => 'data.id',
    );
}
