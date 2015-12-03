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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SlackResourceOwner;

class SlackResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "ok": true,
    "url": "https:\/\/myteam.slack.com\/",
    "team": "My Team",
    "user": "bar",
    "team_id": "T12345",
    "user_id": "1"
}
json;

    protected $paths = array(
        'identifier' => 'user_id',
        'nickname'   => 'user',
    );

    protected $expectedUrls = array(
        'authorization_url'      => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=identify&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=identify&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new SlackResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
