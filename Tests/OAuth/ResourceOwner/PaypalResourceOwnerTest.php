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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\PaypalResourceOwner;

/**
 * Class PaypalResourceOwnerTest
 *
 * @author Berny Cantos <be@rny.cc>
 */
class PaypalResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $userResponse = <<<json
{
    "user_id": "1",
    "email": "bar",
    "name": "Example Default"
}
json;
    protected $paths = array(
        'identifier' => 'user_id',
        'nickname'   => 'email',
        'realname'   => 'name',
    );

    protected $expectedUrls = array(
        'authorization_url' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=openid+email&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=openid+email&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new PaypalResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
