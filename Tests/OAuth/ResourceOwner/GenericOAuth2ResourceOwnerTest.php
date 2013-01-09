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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Request;

class GenericOAuth2ResourceOwnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GenericOAuth2ResourceOwner
     */
    protected $resourceOwner;
    protected $buzzClient;
    protected $buzzResponse;
    protected $buzzResponseContentType;

    protected $userResponse = <<<json
{
    "id":  "1"
    "foo": "bar"
}
json;

    protected $options = array(
        'client_id'           => 'clientid',
        'client_secret'       => 'clientsecret',

        'infos_url'           => 'http://user.info/',
        'authorization_url'   => 'http://user.auth/',
        'access_token_url'    => 'http://user.access/',

        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',

        'scope'               => '',
    );

    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'foo',
        'realname'   => 'foo_disp',
    );

    public function setUp()
    {
        $this->resourceOwner = $this->createResourceOwner('oauth2');
    }

    public function testGetOption()
    {
        $this->assertEquals($this->options['infos_url'], $this->resourceOwner->getOption('infos_url'));
        $this->assertEquals($this->options['client_id'], $this->resourceOwner->getOption('client_id'));
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
        $this->mockBuzz($this->userResponse, 'application/json; charset=utf-8');

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\OAuth\Response\AbstractUserResponse
         */
        $userResponse = $this->resourceOwner->getUserInformation('access_token');

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('access_token', $userResponse->getAccessToken());
    }

    public function testGetAuthorizationUrl()
    {
        $this->assertEquals(
            $this->options['authorization_url'].'?response_type=code&client_id=clientid&scope=&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetAccessToken()
    {
        $this->mockBuzz('access_token=code');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            'code',
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonResponse()
    {
        $this->mockBuzz('{"access_token": "code"}', 'application/json');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            'code',
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonCharsetResponse()
    {
        $this->mockBuzz('{"access_token": "code"}', 'application/json; charset=utf-8');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            'code',
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenFailedResponse()
    {
        $this->mockBuzz('invalid');
        $request = new Request(array('code' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenErrorResponse()
    {
        $this->mockBuzz('error=foo');
        $request = new Request(array('code' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetSetName()
    {
        $this->assertEquals('oauth2', $this->resourceOwner->getName());
        $this->resourceOwner->setName('foo');
        $this->assertEquals('foo', $this->resourceOwner->getName());
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner('oauth2', array('user_response_class' => $class));

        $this->mockBuzz();

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation('access_token');

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
    }

    public function buzzSendMock($request, $response)
    {
        $response->setContent($this->buzzResponse);
        $response->addHeader('Content-Type: ' . $this->buzzResponseContentType);
    }

    protected function mockBuzz($response = '', $contentType = 'text/plain')
    {
        $this->buzzClient->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(array($this, 'buzzSendMock')));
        $this->buzzResponse = $response;
        $this->buzzResponseContentType = $contentType;
    }

    protected function createResourceOwner($name, array $options = array(), array $paths = array())
    {
        $this->buzzClient = $this->getMockBuilder('\Buzz\Client\ClientInterface')
            ->disableOriginalConstructor()->getMock();
        $httpUtils = $this->getMockBuilder('\Symfony\Component\Security\Http\HttpUtils')
            ->disableOriginalConstructor()->getMock();

        $options = array_merge($this->options, $options);
        $resourceOwner = $this->setUpResourceOwner($name, $httpUtils, $options);
        if (false !== strpos($options['user_response_class'], '\PathUserResponse')) {
            $resourceOwner->addPaths(array_merge($this->paths, $paths));
        }

        return $resourceOwner;
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new GenericOAuth2ResourceOwner($this->buzzClient, $httpUtils, $options, $name);
    }
}
