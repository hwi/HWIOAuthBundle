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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericResourceOwner;

class GenericResourceOwnerTest extends \PHPUnit_Framework_Testcase
{
    protected $resourceOwner;
    protected $buzzClient;
    protected $buzzResponse;

    protected $userResponse = '{"foo": "bar"}';

    public function setup()
    {
        $this->resourceOwner = $this->createResourceOwner($this->getDefaultOptions(), 'generic');
    }

    protected function getDefaultOptions()
    {
        return array('infos_url' => 'http://user.info/', 
            'client_id' => 'clientid',
            'scope' => '',
            'authorization_url' => 'http://user.auth/',
            'access_token_url' => 'http://user.access/',
            'client_secret' => 'clientsecret',
        );
    }

    protected function getDefaultPaths()
    {
        return array('username' => 'foo',
            'displayname' => 'foo_disp',
        );
    }

    protected function createResourceOwner(array $options, $name, $paths = null)
    {
        $this->buzzClient = $this->getMockBuilder('\Buzz\Client\ClientInterface')
            ->disableOriginalConstructor()->getMock();
        $httpUtils = $this->getMockBuilder('\Symfony\Component\Security\Http\HttpUtils')
            ->disableOriginalConstructor()->getMock();

        return new GenericResourceOwner($this->buzzClient, $httpUtils, $options, $name, $paths ?: $this->getDefaultPaths());
    }

    public function testGetOption()
    {
        $this->assertEquals('http://user.info/', $this->resourceOwner->getOption('infos_url'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetInvalidOptionThrowsException()
    {
        $this->resourceOwner->getOption('non_existing');
    }

    public function testGetUserInformation()
    {
        $this->mockBuzz($this->userResponse);
        $userResponse = $this->resourceOwner->getUserInformation('access_token');

        $this->assertEquals('bar', $userResponse->getUsername());
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            'http://user.auth/?response_type=code&client_id=clientid&scope=&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetAccessToken()
    {
        $this->mockBuzz('access_token=code');
        $accessToken = $this->resourceOwner->getAccessToken('code', 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenFailedResponse()
    {
        $this->mockBuzz('invalid');
        $accessToken = $this->resourceOwner->getAccessToken('code', 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenErrorResponse()
    {
        $this->mockBuzz('error=foo');
        $accessToken = $this->resourceOwner->getAccessToken('code', 'http://redirect.to/');
    }

    public function testGetSetName()
    {
        $this->assertEquals('generic', $this->resourceOwner->getName());
        $this->resourceOwner->setName('foo');
        $this->assertEquals('foo', $this->resourceOwner->getName());
    }

    public function testCustomResponseClass()
    {
        $options = $this->getDefaultOptions();
        $options['user_response_class'] = "\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse";
        $resourceOwner = $this->createResourceOwner($options, 'generic');

        $this->mockBuzz();
        $userResponse = $resourceOwner->getUserInformation('access_token');

        $this->assertInstanceOf($options['user_response_class'], $userResponse);
        $this->assertEquals('foo', $userResponse->getUsername());
    }

    protected function mockBuzz($response = '')
    {
        $this->buzzClient->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(array($this, 'buzzSendMock')));
        $this->buzzResponse = $response;
    }

    public function buzzSendMock($request, $response)
    {
        $response->setContent($this->buzzResponse);
    }
}
