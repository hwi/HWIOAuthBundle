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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\YandexResourceOwner;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class YandexResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = YandexResourceOwner::class;
    protected string $userResponse = <<<json
{
    "id": "1",
    "display_name": "bar",
    "real_name": "baz"
}
json;

    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'display_name',
        'realname' => 'real_name',
    ];
}
