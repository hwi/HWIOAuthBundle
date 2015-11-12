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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\YandexResourceOwner;

class YandexResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "id": "1",
    "display_name": "bar",
    "real_name": "baz"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'display_name',
        'realname'   => 'real_name',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new YandexResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
