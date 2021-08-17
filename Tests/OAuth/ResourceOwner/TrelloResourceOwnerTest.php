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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TrelloResourceOwner;

final class TrelloResourceOwnerTest extends GenericOAuth1ResourceOwnerTest
{
    protected string $resourceOwnerClass = TrelloResourceOwner::class;
    protected string $userResponse = <<<json
{
    "id": "1",
    "username": "bar",
    "fullName": "foo"
}
json;
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
        'realname' => 'fullName',
        'email' => 'email',
        'profilepicture' => 'avatarSource',
    ];

    public function testGetAuthorizationUrlContainOAuthTokenAndSecret(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json; charset=utf-8'),
            ]
        );

        $this->storage->expects($this->once())
            ->method('save')
            ->with($resourceOwner, ['oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'timestamp' => time()]);

        $this->assertEquals(
            'http://user.auth/?test=3&scope=read&oauth_token=token',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }
}
