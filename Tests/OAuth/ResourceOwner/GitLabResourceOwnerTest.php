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

class GitLabResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $resourceOwnerClass = GitLabResourceOwner::class;
    protected $httpClientCalls = 1;

    protected $authorizationUrlBasePart = 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=read_user';

    public function testRevokeToken()
    {
        $this->httpResponseHttpCode = 200;
        $this->mockHttpClient(null, 'application/json');

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }
}
