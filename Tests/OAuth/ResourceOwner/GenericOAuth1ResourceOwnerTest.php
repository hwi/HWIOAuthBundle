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
    protected $buzzClient;
    protected $buzzResponse;
    protected $buzzResponseContentType;
    protected $storage;

    protected $userResponse = '{"id": "1", "foo": "bar"}';
    protected $options = array(
        'client_id'           => 'clientid',
        'client_secret'       => 'clientsecret',

        'infos_url'           => 'http://user.info/',
        'request_token_url'   => 'http://user.request/',
        'authorization_url'   => 'http://user.auth/',
        'access_token_url'    => 'http://user.access/',

        'user_response_class' => '\HWI\Bundle\OAuthBundle\OAuth\Response\PathUserResponse',

        'realm'               => null,
        'scope'               => null,
    );

    protected $paths = array(
        'identifier' => 'id',
        'nickname'   => 'foo',
        'realname'   => 'foo_disp',
    );

    public function setUp()
    {
        $this->resourceOwner = $this->createResourceOwner('oauth1');
    }

    public function testGetOption()
    {
        $this->assertEquals($this->options['infos_url'], $this->resourceOwner->getOption('infos_url'));
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

        $accessToken  = array('oauth_token' => 'token', 'oauth_token_secret' => 'secret');
        $userResponse = $this->resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals($accessToken, $userResponse->getAccessToken());
    }

    public function testGetAuthorizationUrlContainOAuthTokenAndSecret()
    {
        $this->mockBuzz('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json; charset=utf-8');

        $this->storage->expects($this->once())
            ->method('save')
            ->with($this->resourceOwner, array('oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'timestamp' => time()));

        $this->assertEquals(
            $this->options['authorization_url'].'?oauth_token=token',
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

        $request = new Request(array('oauth_token' => 'token', 'oauth_verifier' => 'code'));

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetSetName()
    {
        $this->assertEquals('oauth1', $this->resourceOwner->getName());
        $this->resourceOwner->setName('foo');
        $this->assertEquals('foo', $this->resourceOwner->getName());
    }

    public function testCustomResponseClass()
    {
        $class         = '\HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse';
        $resourceOwner = $this->createResourceOwner('oauth1', array('user_response_class' => $class));

        $this->mockBuzz();

        /**
         * @var $userResponse \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse
         */
        $userResponse = $resourceOwner->getUserInformation(array('oauth_token' => 'token', 'oauth_token_secret' => 'secret'));

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

        $this->storage = $this->getMock('\HWI\Bundle\OAuthBundle\OAuth\OAuth1RequestTokenStorageInterface');

        $resourceOwner = $this->setUpResourceOwner($name, $httpUtils, array_merge($this->options, $options));
        $resourceOwner->addPaths(array_merge($this->paths, $paths));

        return $resourceOwner;
    }

    protected function setUpResourceOwner($name, $httpUtils, array $options)
    {
        return new GenericOAuth1ResourceOwner($this->buzzClient, $httpUtils, $options, $name, $this->storage);
    }
}
