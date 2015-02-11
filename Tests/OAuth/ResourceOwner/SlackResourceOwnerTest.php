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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\SlackResourceOwner;

class SlackResourceOwnerTest extends GenericOAuth2ResourceOwnerTest
{
    protected $infoResponse = <<<json
{
    "user_id": "1",
    "user": "bar"
}
json;

    protected $userResponse = <<<json
{
	"user": {
		"id": "1",
		"name": "bar"
	}
}
json;

    protected $paths = array(
        'identifier'     => 'id',
        'nickname'       => 'name',
        'realname'       => 'profile.real_name',
        'email'          => 'profile.email',
    );

	protected $isFirstCall = true;

    protected $expectedUrls = array(
        'authorization_url'      => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=identify+read+post&redirect_uri=http%3A%2F%2Fredirect.to%2F&team=',
        'authorization_url_csrf' => 'http://user.auth/?test=2&response_type=code&client_id=clientid&scope=identify+read+post&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F&team=',
    );

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new SlackResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }

    public function testGetUserInformation()
    {
        $this->buzzClient->expects($this->exactly(2))
            ->method('send')
            ->will($this->returnCallback(array($this, 'buzzSendMockSlackUser')));
        $this->buzzResponseContentType = 'application/json; charset=utf-8';

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('user_response_class' => $class));

        $this->buzzClient->expects($this->exactly(2))
            ->method('send')
            ->will($this->returnCallback(array($this, 'buzzSendMockSlackUser')));
        $this->buzzResponseContentType = 'application/json; charset=utf-8';

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function buzzSendMockSlackUser($request, $response)
    {
		if ($this->isFirstCall) {
			$this->isFirstCall = false;
			$response->setContent($this->infoResponse);
		} else {
			$this->isFirstCall = true;
			$response->setContent($this->userResponse);
		}
        $response->addHeader('HTTP/1.1 '.$this->buzzResponseHttpCode.' Some text');
        $response->addHeader('Content-Type: '.$this->buzzResponseContentType);
    }
}
