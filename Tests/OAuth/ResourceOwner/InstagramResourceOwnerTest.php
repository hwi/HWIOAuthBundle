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
use HWI\Bundle\OAuthBundle\Test\Fixtures\CustomUserResponse;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class InstagramResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = InstagramResourceOwner::class;
    protected string $userResponse = <<<json
{
    "id": "1",
    "username": "bar"
}
json;
    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'username',
    ];

    public function testCustomResponseClass(): void
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /* @var $userResponse CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation(['access_token' => 'token']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }
}
