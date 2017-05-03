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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\ThirtySevenSignalsResourceOwner;

class ThirtySevenSignalsResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = ThirtySevenSignalsResourceOwner::class;
    protected $userResponse = <<<json
{
    "expires_at": "2014-03-22T16:56:48-05:00",
    "identity": {
        "id": 1,
        "email_address": "bar"
    }
}
json;
    protected $paths = array(
        'identifier' => 'identity.id',
        'nickname' => 'identity.email_address',
        'firstname' => 'identity.first_name',
        'lastname' => 'identity.last_name',
        'realname' => array('identity.last_name', 'identity.first_name'),
        'email' => 'identity.email_address',
    );

    protected $expectedUrls = array(
        'authorization_url' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&redirect_uri=http%3A%2F%2Fredirect.to%2F&type=web_server',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&type=web_server',
    );
}
