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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\ToshlResourceOwner;

class ToshlResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = ToshlResourceOwner::class;
    protected $csrf = true;
    protected $userResponse = <<<json
{
    "id": "1",
    "email": "example@website.com",
    "first_name": "John",
    "last_name": "Smith"
}
json;

    protected $paths = [
        'identifier' => 'id',
        'nickname' => 'email',
        'firstname' => 'first_name',
        'lastname' => 'last_name',
        'realname' => ['first_name', 'last_name'],
        'email' => 'email',
    ];

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

    public function testGetUserInformation()
    {
        $this->mockHttpClient($this->userResponse, 'application/json; charset=utf-8');

        /**
         * @var \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('example@website.com', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertEquals('John', $userResponse->getFirstName());
        $this->assertEquals('Smith', $userResponse->getLastName());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
