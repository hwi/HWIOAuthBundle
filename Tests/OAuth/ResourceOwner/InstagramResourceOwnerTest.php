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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\InstagramResourceOwner;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;

class InstagramResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = InstagramResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "username": "bar"
}
json;
    protected $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
    ];

    protected $expectedUrls = [
        'authorization_url' => 'http://user.auth/?test=2&response_type=code&app_id=clientid&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&app_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
    ];

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, ['user_response_class' => $class]);

        /* @var $userResponse CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }
}
