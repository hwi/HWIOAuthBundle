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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GitHubResourceOwner;

class GitHubResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = GitHubResourceOwner::class;
    protected $userResponse = <<<json
{
    "id": "1",
    "login": "bar"
}
json;

    protected $paths = array(
        'identifier' => 'id',
        'nickname' => 'login',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'avatar_url',
    );

    protected $httpClientCalls = 1;

    public function testRevokeToken()
    {
        $this->httpResponseHttpCode = 204;
        $this->mockHttpClient(null, 'application/json');

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $this->httpResponseHttpCode = 404;
        $this->mockHttpClient('{"id": "666"}', 'application/json');

        $this->assertFalse($this->resourceOwner->revokeToken('token'));
    }

    public function testCustomResponseClass()
    {
        $this->httpClientCalls = 2;

        parent::testCustomResponseClass();

        $this->httpClientCalls = 1;
    }

    public function testGetUserInformation()
    {
        $this->httpClientCalls = 2;

        parent::testGetUserInformation();

        $this->httpClientCalls = 1;
    }
}
