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
    private $buzzCalls = 1;

    protected $userResponse = <<<json
{
    "id": "1",
    "login": "bar"
}
json;

    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'login',
        'realname'       => 'name',
        'email'          => 'email',
        'profilepicture' => 'avatar_url',
    );

    public function testRevokeToken()
    {
        $this->buzzResponseHttpCode = 204;
        $this->mockBuzz(null, 'application/json');

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $this->buzzResponseHttpCode = 404;
        $this->mockBuzz('{"id": "666"}', 'application/json');

        $this->assertFalse($this->resourceOwner->revokeToken('token'));
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new GitHubResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }

    public function testCustomResponseClass()
    {
        $this->buzzCalls = 2;

        parent::testCustomResponseClass();

        $this->buzzCalls = 1;
    }

    public function testGetUserInformation()
    {
        $this->buzzCalls = 2;

        parent::testGetUserInformation();

        $this->buzzCalls = 1;
    }

    protected function mockBuzz($response = '', $contentType = 'text/plain')
    {
        $this->buzzClient->expects($this->exactly($this->buzzCalls))
            ->method('send')
            ->will($this->returnCallback(array($this, 'buzzSendMock')));
        $this->buzzResponse = $response;
        $this->buzzResponseContentType = $contentType;
    }
}
