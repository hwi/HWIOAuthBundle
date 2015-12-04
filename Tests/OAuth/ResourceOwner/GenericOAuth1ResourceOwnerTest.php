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

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner;
use Symfony\Component\HttpFoundation\Request;

class GenericOAuth1ResourceOwnerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GenericOAuth1ResourceOwner
     */
    protected $resourceOwner;
    protected $resourceOwnerName;
    protected $buzzClient;
    protected $buzzResponse;
    protected $buzzResponseContentType;
    protected $storage;

    protected $options = array(
        'client_id'           => 'clientid',
        'client_secret'       => 'clientsecret',

        'infos_url'           => 'http://user.info/?test=1',
        'request_token_url'   => 'http://user.request/?test=2',
        'authorization_url'   => 'http://user.auth/?test=3',
        'access_token_url'    => 'http://user.access/?test=4',
    );

    protected $userResponse = '{"id": "1", "foo": "bar"}';

    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'foo',
        'realname'   => 'foo_disp',
    );

    public function setUp()
    {
        $this->resourceOwnerName = str_replace(array('generic', 'resourceownertest'), '', strtolower(__CLASS__));
        $this->resourceOwner     = $this->createResourceOwner($this->resourceOwnerName);
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function testUndefinedOptionThrowsException()
    {
        $this->createResourceOwner($this->resourceOwnerName, array('non_existing' => null));
    }

    /**
     * @expectedException \Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     */
    public function testInvalidOptionValueThrowsException()
    {
        $this->createResourceOwner($this->resourceOwnerName, array('csrf' => 'invalid'));
    }

    public function testHandleRequest()
    {
        $request = new Request(array('test' => 'test'));

        $this->assertFalse($this->resourceOwner->handles($request));

        $request = new Request(array('oauth_token' => 'test'));

        $this->assertTrue($this->resourceOwner->handles($request));

        $request = new Request(array('oauth_token' => 'test', 'test' => 'test'));

        $this->assertTrue($this->resourceOwner->handles($request));
    }

    public function testGetUserInformation()
    {
        $this->mockBuzz($this->userResponse, 'application/json; charset=utf-8');

        $accessToken  = array('oauth_token' => 'token', 'oauth_token_secret' => 'secret');
        $userResponse = $this->resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetAuthorizationUrlContainOAuthTokenAndSecret()
    {
        $this->mockBuzz('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json; charset=utf-8');

        $this->storage->expects($this->once())
            ->method('save')
            ->with($this->resourceOwner, array('oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'timestamp' => time()));

        $this->assertEquals(
            $this->options['authorization_url'].'&oauth_token=token',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAuthorizationUrlFailedResponseContainOnlyOAuthToken()
    {
        $this->mockBuzz('{"oauth_token": "token"}', 'application/json; charset=utf-8');

        $this->storage->expects($this->never())
            ->method('save');

        $this->resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAuthorizationUrlFailedResponseContainOAuthProblem()
    {
        $this->mockBuzz('oauth_problem=message');

        $this->storage->expects($this->never())
            ->method('save');

        $this->resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAuthorizationUrlFailedResponseContainCallbackNotConfirmed()
    {
        $this->mockBuzz('oauth_callback_confirmed=false');

        $this->storage->expects($this->never())
            ->method('save');

        $this->resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAuthorizationUrlFailedResponseNotContainOAuthTokenOrSecret()
    {
        $this->mockBuzz('invalid');

        $this->storage->expects($this->never())
            ->method('save');

        $this->resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    public function testGetAccessToken()
    {
        $this->mockBuzz('oauth_token=token&oauth_token_secret=secret');

        $request = new Request(array('oauth_verifier' => 'code', 'oauth_token' => 'token'));

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($this->resourceOwner, 'token')
            ->will($this->returnValue(array('oauth_token' => 'token2', 'oauth_token_secret' => 'secret2')));

        $this->assertEquals(
            array('oauth_token' => 'token', 'oauth_token_secret' => 'secret'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonResponse()
    {
        $this->mockBuzz('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json');

        $request = new Request(array('oauth_verifier' => 'code', 'oauth_token' => 'token'));

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($this->resourceOwner, 'token')
            ->will($this->returnValue(array('oauth_token' => 'token2', 'oauth_token_secret' => 'secret2')));

        $this->assertEquals(
            array('oauth_token' => 'token', 'oauth_token_secret' => 'secret'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonCharsetResponse()
    {
        $this->mockBuzz('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json; charset=utf-8');

        $request = new Request(array('oauth_verifier' => 'code', 'oauth_token' => 'token'));

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($this->resourceOwner, 'token')
            ->will($this->returnValue(array('oauth_token' => 'token2', 'oauth_token_secret' => 'secret2')));

        $this->assertEquals(
            array('oauth_token' => 'token', 'oauth_token_secret' => 'secret'),
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenFailedResponse()
    {
        $this->mockBuzz('invalid');

        $this->storage->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(array('oauth_token' => 'token', 'oauth_token_secret' => 'secret')));

        $this->storage->expects($this->never())
            ->method('save');

        $request = new Request(array('oauth_token' => 'token', 'oauth_verifier' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenErrorResponse()
    {
        $this->mockBuzz('error=foo');

        $this->storage->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(array('oauth_token' => 'token', 'oauth_token_secret' => 'secret')));

        $this->storage->expects($this->never())
            ->method('save');

        $request = new Request(array('oauth_token' => 'token', 'oauth_verifier' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testGetAccessTokenInvalidArgumentException()
    {
        $this->storage->expects($this->once())
            ->method('fetch')
            ->will($this->throwException(new \InvalidArgumentException));

        $this->buzzClient->expects($this->never())
            ->method('send');

        $this->storage->expects($this->never())
            ->method('save');

        $request = new Request(array('oauth_token' => 'token', 'oauth_verifier' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testRefreshAccessToken()
    {
        $this->resourceOwner->refreshAccessToken('token');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function testRevokeToken()
    {
        $this->resourceOwner->revokeToken('token');
    }

    public function testCsrfTokenIsAlwaysValidForOAuth1()
    {
        $this->storage->expects($this->never())
            ->method('fetch');

        $this->assertTrue($this->resourceOwner->isCsrfTokenValid('valid_token'));
    }

    public function testCsrfTokenValid()
    {
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('csrf' => true));

        $this->storage->expects($this->never())
            ->method('fetch');

        $this->assertTrue($resourceOwner->isCsrfTokenValid('valid_token'));
    }

    public function testGetSetName()
    {
        $this->assertEquals($this->resourceOwnerName, $this->resourceOwner->getName());
        $this->resourceOwner->setName('foo');
        $this->assertEquals('foo', $this->resourceOwner->getName());
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, array('user_response_class' => $class));

        $this->mockBuzz();

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(array('oauth_token' => 'token', 'oauth_token_secret' => 'secret'));

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
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

        $this->storage = $this->getMock('\HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface');

        $resourceOwner = $this->setUpResourceOwner($name, $httpUtils, array_merge($this->options, $options));
        $resourceOwner->addPaths(array_merge($this->paths, $paths));

        return $resourceOwner;
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new GenericOAuth1ResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
