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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GitLabResourceOwner;

class GitLabResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    private $buzzCalls = 1;

    protected $expectedUrls = array(
        'authorization_url' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=api&redirect_uri=http%3A%2F%2Fredirect.to%2F',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=api&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
    );

    public function testRevokeToken()
    {
        $this->buzzResponseHttpCode = 200;
        $this->mockBuzz(null, 'application/json');

        $this->assertTrue($this->resourceOwner->revokeToken('token'));
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new GitLabResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
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
