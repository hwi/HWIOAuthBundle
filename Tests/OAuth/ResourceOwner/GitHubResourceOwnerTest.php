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

use Fig\Http\Message\StatusCodeInterface;
use GuzzleHttp\Psr7\Response;
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
        'identifier' => 'id',
        'nickname' => 'login',
        'realname' => 'name',
        'email' => 'email',
        'profilepicture' => 'avatar_url',
    );

    public function testRevokeToken()
    {
        $this->mockBuzz('', 'application/json', StatusCodeInterface::STATUS_NO_CONTENT);

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }

    public function testRevokeTokenFails()
    {
        $this->mockBuzz('{"id": "666"}', 'application/json', StatusCodeInterface::STATUS_NOT_FOUND);

        $this->assertFalse($this->resourceOwner->revokeToken('token'));
    }
}
