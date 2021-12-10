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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GitLabResourceOwner;
use HWI\Bundle\OAuthBundle\Test\OAuth\ResourceOwner\GenericOAuth2ResourceOwnerTestCase;

final class GitLabResourceOwnerTest extends GenericOAuth2ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = GitLabResourceOwner::class;
    protected int $httpClientCalls = 1;

    protected string $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=read_user';

    public function testRevokeToken(): void
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json'),
            ]
        );

        $this->assertTrue($resourceOwner->revokeToken('token'));
    }
}
