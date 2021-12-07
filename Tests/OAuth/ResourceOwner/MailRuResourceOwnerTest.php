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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\MailRuResourceOwner;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class MailRuResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = MailRuResourceOwner::class;
    protected string $userResponse = <<<json
[
    {
        "user_id": "1",
        "name": "bar",
        "email": "baz"
    }
]
json;

    protected array $paths = [
        'identifier' => 'user_id',
        'nickname' => 'name',
        'email' => 'email',
    ];
}
