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

use Buzz\Message\MessageInterface;
use Buzz\Message\RequestInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
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
    protected $buzzResponseHttpCode = 200;
    protected $storage;
    protected $state = 'random';

    protected $userResponse = <<<json
{
    "id":  "1"
    "foo": "bar"
}
json;

    protected $options = array(
        'client_id'           => 'clientid',
        'client_secret'       => 'clientsecret',

        'infos_url'           => 'http://user.info/?test=1',
        'authorization_url'   => 'http://user.auth/?test=2',
        'access_token_url'    => 'http://user.access/?test=3',

        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',

        'csrf'                => false,
        'scope'               => null,
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
        $this->assertEquals($this->options['csrf'], $this->resourceOwner->getOption('csrf'));
    }

    public function testGetOptionWithDefaults()
    {
        $buzzClient = $this->getMockBuilder('\Buzz\Client\ClientInterface')
            ->disableOriginalConstructor()->getMock();
        $httpUtils = $this->getMockBuilder('\Symfony\Component\Security\Http\HttpUtils')
            ->disableOriginalConstructor()->getMock();

        $storage = $this->getMock('\HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface');

        $resourceOwner = new GenericOAuth2ResourceOwner($buzzClient, $httpUtils, array(), 'oauth2', $storage);

        $this->assertNull($resourceOwner->getOption('client_id'));
        $this->assertNull($resourceOwner->getOption('client_secret'));

        $this->assertNull($resourceOwner->getOption('infos_url'));

        $this->assertEquals('HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse', $resourceOwner->getOption('user_response_class'));

        $this->assertNull($resourceOwner->getOption('scope'));
        $this->assertFalse($resourceOwner->getOption('csrf'));
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
        $userResponse = $this->resourceOwner->getUserInformation(array('access_token' => 'token'));

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetAuthorizationUrl()
    {
        $this->state = null;

        $resourceOwner = $this->createResourceOwner('oauth2');

        $this->storage->expects($this->never())
            ->method('save');

        $this->assertStringStartsWith(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );

        $this->state = 'random';
    }

    public function testGetAuthorizationUrlWithEnabledCsrf()
    {
        $resourceOwner = $this->createResourceOwner('oauth2', array('csrf' => true));

        $this->storage->expects($this->once())
            ->method('save')
            ->with($resourceOwner, $this->state, 'csrf_state');

        $this->assertStringStartsWith(
            $this->options['authorization_url'].'&response_type=code&client_id=clientid&state=random&redirect_uri=http%3A%2F%2Fredirect.to%2F',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetAccessToken()
    {
        $this->mockBuzz('access_token=code');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonResponse()
    {
        $this->mockBuzz('{"access_token": "code"}', 'application/json');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonCharsetResponse()
    {
        $this->mockBuzz('{"access_token": "code"}', 'application/json; charset=utf-8');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenTextJavascriptResponse()
    {
        $this->mockBuzz('{"access_token": "code"}', 'text/javascript');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenTextJavascriptCharsetResponse()
    {
        $this->mockBuzz('{"access_token": "code"}', 'text/javascript; charset=utf-8');

        $request = new Request(array('code' => 'somecode'));

        $this->assertEquals(
            array('access_token' => 'code'),
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

    public function testRefreshAccessToken()
    {
        $this->mockBuzz('{"access_token": "bar", "expires_in": 3600}', 'application/json');
        $accessToken = $this->resourceOwner->refreshAccessToken('foo');

        $this->assertEquals('bar', $accessToken['access_token']);
        $this->assertEquals(3600, $accessToken['expires_in']);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testRefreshAccessTokenInvalid()
    {
        $this->mockBuzz('invalid');

        $this->resourceOwner->refreshAccessToken('foo');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testRefreshAccessTokenError()
    {
        $this->mockBuzz('{"error": "invalid"}', 'application/json');

        $this->resourceOwner->refreshAccessToken('foo');
    }

    public function testRevokeToken()
    {
        $this->setExpectedException('\Symfony\Component\Security\Core\Exception\AuthenticationException');

        $this->resourceOwner->revokeToken('token');
    }

    public function testGetSetName()
    {
        $this->assertEquals('oauth2', $this->resourceOwner->getName());
        $this->resourceOwner->setName('foo');
        $this->assertEquals('foo', $this->resourceOwner->getName());
    }

    public function testCsrfTokenIsValidWhenDisabled()
    {
        $this->storage->expects($this->never())
            ->method('fetch');

        $this->assertFalse($this->resourceOwner->getOption('csrf'));
        $this->assertTrue($this->resourceOwner->isCsrfTokenValid('whatever you want'));
    }

    public function testCsrfTokenValid()
    {
        $resourceOwner = $this->createResourceOwner('oauth2', array('csrf' => true));

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, 'valid_token', 'csrf_state')
            ->will($this->returnValue('valid_token'));

        $this->assertTrue($resourceOwner->getOption('csrf'));
        $this->assertTrue($resourceOwner->isCsrfTokenValid('valid_token'));
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testCsrfTokenInvalid()
    {
        $resourceOwner = $this->createResourceOwner('oauth2', array('csrf' => true));

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, 'invalid_token', 'csrf_state')
            ->will($this->throwException(new \InvalidArgumentException('No data available in storage.')));

        $resourceOwner->isCsrfTokenValid('invalid_token');
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner('oauth2', array('user_response_class' => $class));

        $this->mockBuzz();

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

    /**
     * @param RequestInterface $request
     * @param MessageInterface $response
     */
    public function buzzSendMock($request, $response)
    {
        $response->setContent($this->buzzResponse);
        $response->addHeader('HTTP/1.1 '.$this->buzzResponseHttpCode.' Some text');
        $response->addHeader('Content-Type: '.$this->buzzResponseContentType);
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

        $this->storage = $this->getMock('\HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface');

        $options = array_merge($this->options, $options);
        $resourceOwner = $this->setUpResourceOwner($name, $httpUtils, $options);
        if (false !== strpos($options['user_response_class'], '\PathUserResponse')) {
            $resourceOwner->addPaths(array_merge($this->paths, $paths));
        }

        $reflection = new \ReflectionClass(get_class($resourceOwner));
        $stateProperty = $reflection->getProperty('state');
        $stateProperty->setAccessible(true);
        $stateProperty->setValue($resourceOwner, $this->state);

        return $resourceOwner;
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new GenericOAuth2ResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
