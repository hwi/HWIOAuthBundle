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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\VkontakteResourceOwner;

class VkontakteResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "response": {
        "user_id": "1",
        "user_name": "bar"
    }
}
json;

    protected $paths = array(
        'identifier' => 'response.user_id',
        'nickname'   => 'response.user_name',
        'realname'   => 'response.user_name',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new VkontakteResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
