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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\FoursquareResourceOwner;

class FoursquareResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "response": {
        "user": {
            "id": "1",
            "firstName": "bar",
            "lastName": "foo"
        }
    }
}
json;

    protected $paths = array(
        'identifier' => 'response.user.id',
        'nickname'   => 'response.user.firstName',
        'realname'   => array('response.user.firstName', 'response.user.lastName'),
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new FoursquareResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
