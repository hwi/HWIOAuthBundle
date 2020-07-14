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

use Http\Client\Common\HttpMethodsClient;
use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

class GenericOAuth1ResourceOwnerTest extends ResourceOwnerTestCase
{
    protected $resourceOwnerClass = GenericOAuth1ResourceOwner::class;
    /** @var GenericOAuth1ResourceOwner */
    protected $resourceOwner;
    protected $resourceOwnerName;
    /** @var \PHPUnit_Framework_MockObject_MockObject|HttpMethodsClient */
    protected $httpClient;
    protected $httpResponse;
    protected $httpResponseContentType;
    /** @var \PHPUnit_Framework_MockObject_MockObject|RequestDataStorageInterface */
    protected $storage;

    protected $options = [
        'client_id' => 'clientid',
        'client_secret' => 'clientsecret',

        'infos_url' => 'http://user.info/?test=1',
        'request_token_url' => 'http://user.request/?test=2',
        'authorization_url' => 'http://user.auth/?test=3',
        'access_token_url' => 'http://user.access/?test=4',
    ];

    protected $userResponse = '{"id": "1", "foo": "bar"}';

    protected $paths = [
        'identifier' => 'id',
        'nickname' => 'foo',
        'realname' => 'foo_disp',
    ];

    protected function setUp(): void
    {
        $this->resourceOwnerName = str_replace(['generic', 'resourceownertest'], '', strtolower(__CLASS__));
        $this->resourceOwner = $this->createResourceOwner($this->resourceOwnerName);
    }

    public function testUndefinedOptionThrowsException()
    {
        $this->expectException(ExceptionInterface::class);

        $this->createResourceOwner($this->resourceOwnerName, ['non_existing' => null]);
    }

    public function testInvalidOptionValueThrowsException()
    {
        $this->expectException(ExceptionInterface::class);

        $this->createResourceOwner($this->resourceOwnerName, ['csrf' => 'invalid']);
    }

    public function testHandleRequest()
    {
        $request = new Request(['test' => 'test']);

        $this->assertFalse($this->resourceOwner->handles($request));

        $request = new Request(['oauth_token' => 'test']);

        $this->assertTrue($this->resourceOwner->handles($request));

        $request = new Request(['oauth_token' => 'test', 'test' => 'test']);

        $this->assertTrue($this->resourceOwner->handles($request));
    }

    public function testGetUserInformation()
    {
        $this->mockHttpClient($this->userResponse, 'application/json; charset=utf-8');

        $accessToken = ['oauth_token' => 'token', 'oauth_token_secret' => 'secret'];
        $userResponse = $this->resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetAuthorizationUrlContainOAuthTokenAndSecret()
    {
        $this->mockHttpClient('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json; charset=utf-8');

        $this->storage->expects($this->once())
            ->method('save')
            ->with($this->resourceOwner, ['oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'timestamp' => time()]);

        $this->assertEquals(
            $this->options['authorization_url'].'&oauth_token=token',
            $this->resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetAuthorizationUrlFailedResponseContainOnlyOAuthToken()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $this->mockHttpClient('{"oauth_token": "token"}', 'application/json; charset=utf-8');

        $this->storage->expects($this->never())
            ->method('save');

        $this->resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    public function testGetAuthorizationUrlFailedResponseContainOAuthProblem()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $this->mockHttpClient('oauth_problem=message');

        $this->storage->expects($this->never())
            ->method('save');

        $this->resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    public function testGetAuthorizationUrlFailedResponseContainCallbackNotConfirmed()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $this->mockHttpClient('oauth_callback_confirmed=false');

        $this->storage->expects($this->never())
            ->method('save');

        $this->resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    public function testGetAuthorizationUrlFailedResponseNotContainOAuthTokenOrSecret()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $this->mockHttpClient('invalid');

        $this->storage->expects($this->never())
            ->method('save');

        $this->resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    public function testGetAccessToken()
    {
        $this->mockHttpClient('oauth_token=token&oauth_token_secret=secret');

        $request = new Request(['oauth_verifier' => 'code', 'oauth_token' => 'token']);

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($this->resourceOwner, 'token')
            ->willReturn(['oauth_token' => 'token2', 'oauth_token_secret' => 'secret2']);

        $this->assertEquals(
            ['oauth_token' => 'token', 'oauth_token_secret' => 'secret'],
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonResponse()
    {
        $this->mockHttpClient('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json');

        $request = new Request(['oauth_verifier' => 'code', 'oauth_token' => 'token']);

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($this->resourceOwner, 'token')
            ->willReturn(['oauth_token' => 'token2', 'oauth_token_secret' => 'secret2']);

        $this->assertEquals(
            ['oauth_token' => 'token', 'oauth_token_secret' => 'secret'],
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonCharsetResponse()
    {
        $this->mockHttpClient('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json; charset=utf-8');

        $request = new Request(['oauth_verifier' => 'code', 'oauth_token' => 'token']);

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($this->resourceOwner, 'token')
            ->willReturn(['oauth_token' => 'token2', 'oauth_token_secret' => 'secret2']);

        $this->assertEquals(
            ['oauth_token' => 'token', 'oauth_token_secret' => 'secret'],
            $this->resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenFailedResponse()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $this->mockHttpClient('invalid');

        $this->storage->expects($this->once())
            ->method('fetch')
            ->willReturn(['oauth_token' => 'token', 'oauth_token_secret' => 'secret']);

        $this->storage->expects($this->never())
            ->method('save');

        $request = new Request(['oauth_token' => 'token', 'oauth_verifier' => 'code']);

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetAccessTokenErrorResponse()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $this->mockHttpClient('error=foo');

        $this->storage->expects($this->once())
            ->method('fetch')
            ->willReturn(['oauth_token' => 'token', 'oauth_token_secret' => 'secret']);

        $this->storage->expects($this->never())
            ->method('save');

        $request = new Request(['oauth_token' => 'token', 'oauth_verifier' => 'code']);

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetAccessTokenInvalidArgumentException()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $this->storage->expects($this->once())
            ->method('fetch')
            ->will($this->throwException(new \InvalidArgumentException()));

        $this->httpClient->expects($this->never())
            ->method('sendRequest');

        $this->storage->expects($this->never())
            ->method('save');

        $request = new Request(['oauth_token' => 'token', 'oauth_verifier' => 'code']);

        $this->resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testRefreshAccessToken()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

        $this->resourceOwner->refreshAccessToken('token');
    }

    public function testRevokeToken()
    {
        $this->expectException(\Symfony\Component\Security\Core\Exception\AuthenticationException::class);

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
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, ['csrf' => true]);

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
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner($this->resourceOwnerName, ['user_response_class' => $class]);

        $this->mockHttpClient();

        /** @var $userResponse CustomUserResponse */
        $userResponse = $resourceOwner->getUserInformation(['oauth_token' => 'token', 'oauth_token_secret' => 'secret']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
