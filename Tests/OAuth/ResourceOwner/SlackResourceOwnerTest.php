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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SlackResourceOwner;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class SlackResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = SlackResourceOwner::class;
    protected string $userResponse = <<<json
{
    "ok": true,
    "url": "https:\/\/myteam.slack.com\/",
    "team": "My Team",
    "user": "bar",
    "team_id": "T12345",
    "user_id": "1"
}
json;

    protected array $paths = [
        'identifier' => 'user_id',
        'nickname' => 'user',
    ];

    protected string $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=identify';
}
