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

use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class GenericOAuth1ResourceOwnerTest extends ResourceOwnerTestCase
{
    protected string $resourceOwnerClass = GenericOAuth1ResourceOwner::class;

    /** @var MockObject&RequestDataStorageInterface */
    protected $storage;

    protected array $options = [
        'client_id' => 'clientid',
        'client_secret' => 'clientsecret',

        'infos_url' => 'http://user.info/?test=1',
        'request_token_url' => 'http://user.request/?test=2',
        'authorization_url' => 'http://user.auth/?test=3',
        'access_token_url' => 'http://user.access/?test=4',
    ];

    protected $userResponse = '{"id": "1", "foo": "bar"}';

    protected array $paths = [
        'identifier' => 'id',
        'nickname' => 'foo',
        'realname' => 'foo_disp',
    ];

    public function testUndefinedOptionThrowsException()
    {
        $this->expectException(ExceptionInterface::class);

        $this->createResourceOwner(['non_existing' => null]);
    }

    public function testInvalidOptionValueThrowsException()
    {
        $this->expectException(ExceptionInterface::class);

        $this->createResourceOwner(['csrf' => 'invalid']);
    }

    public function testHandleRequest()
    {
        $request = new Request(['test' => 'test']);

        $resourceOwner = $this->createResourceOwner();

        $this->assertFalse($resourceOwner->handles($request));

        $request = new Request(['oauth_token' => 'test']);

        $this->assertTrue($resourceOwner->handles($request));

        $request = new Request(['oauth_token' => 'test', 'test' => 'test']);

        $this->assertTrue($resourceOwner->handles($request));
    }

    public function testGetUserInformation()
    {
        $accessToken = ['oauth_token' => 'token', 'oauth_token_secret' => 'secret'];

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse($this->userResponse, 'application/json; charset=utf-8'),
            ]
        );
        $userResponse = $resourceOwner->getUserInformation($accessToken);

        $this->assertEquals('1', $userResponse->getUsername());
        $this->assertEquals('bar', $userResponse->getNickname());
        $this->assertEquals($accessToken['oauth_token'], $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }

    public function testGetAuthorizationUrlContainOAuthTokenAndSecret()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json; charset=utf-8'),
            ]
        );

        $this->storage->expects($this->once())
            ->method('save')
            ->with($resourceOwner, ['oauth_token' => 'token', 'oauth_token_secret' => 'secret', 'timestamp' => time()]);

        $this->assertEquals(
            $this->options['authorization_url'].'&oauth_token=token',
            $resourceOwner->getAuthorizationUrl('http://redirect.to/')
        );
    }

    public function testGetAuthorizationUrlFailedResponseContainOnlyOAuthToken()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"oauth_token": "token"}', 'application/json; charset=utf-8'),
            ]
        );

        $this->storage->expects($this->never())
            ->method('save');

        $resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    public function testGetAuthorizationUrlFailedResponseContainOAuthProblem()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('oauth_problem=message', 'text/plain'),
            ]
        );

        $this->storage->expects($this->never())
            ->method('save');

        $resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    public function testGetAuthorizationUrlFailedResponseContainCallbackNotConfirmed()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('oauth_callback_confirmed=false', 'text/plain'),
            ]
        );

        $this->storage->expects($this->never())
            ->method('save');

        $resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    public function testGetAuthorizationUrlFailedResponseNotContainOAuthTokenOrSecret()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('invalid', 'text/plain'),
            ]
        );

        $this->storage->expects($this->never())
            ->method('save');

        $resourceOwner->getAuthorizationUrl('http://redirect.to/');
    }

    public function testGetAccessToken()
    {
        $request = new Request(['oauth_verifier' => 'code', 'oauth_token' => 'token']);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('oauth_token=token&oauth_token_secret=secret', 'text/plain'),
            ]
        );

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, 'token')
            ->willReturn(['oauth_token' => 'token2', 'oauth_token_secret' => 'secret2']);

        $this->assertEquals(
            ['oauth_token' => 'token', 'oauth_token_secret' => 'secret'],
            $resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonResponse()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json'),
            ]
        );

        $request = new Request(['oauth_verifier' => 'code', 'oauth_token' => 'token']);

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, 'token')
            ->willReturn(['oauth_token' => 'token2', 'oauth_token_secret' => 'secret2']);

        $this->assertEquals(
            ['oauth_token' => 'token', 'oauth_token_secret' => 'secret'],
            $resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenJsonCharsetResponse()
    {
        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('{"oauth_token": "token", "oauth_token_secret": "secret"}', 'application/json; charset=utf-8'),
            ]
        );

        $request = new Request(['oauth_verifier' => 'code', 'oauth_token' => 'token']);

        $this->storage->expects($this->once())
            ->method('fetch')
            ->with($resourceOwner, 'token')
            ->willReturn(['oauth_token' => 'token2', 'oauth_token_secret' => 'secret2']);

        $this->assertEquals(
            ['oauth_token' => 'token', 'oauth_token_secret' => 'secret'],
            $resourceOwner->getAccessToken($request, 'http://redirect.to/')
        );
    }

    public function testGetAccessTokenFailedResponse()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('invalid', 'text/plain'),
            ]
        );

        $this->storage->expects($this->once())
            ->method('fetch')
            ->willReturn(['oauth_token' => 'token', 'oauth_token_secret' => 'secret']);

        $this->storage->expects($this->never())
            ->method('save');

        $request = new Request(['oauth_token' => 'token', 'oauth_verifier' => 'code']);

        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetAccessTokenErrorResponse()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner(
            [],
            [],
            [
                $this->createMockResponse('error=foo', 'text/plain'),
            ]
        );

        $this->storage->expects($this->once())
            ->method('fetch')
            ->willReturn(['oauth_token' => 'token', 'oauth_token_secret' => 'secret']);

        $this->storage->expects($this->never())
            ->method('save');

        $request = new Request(['oauth_token' => 'token', 'oauth_verifier' => 'code']);

        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testGetAccessTokenInvalidArgumentException()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner();

        $this->storage->expects($this->once())
            ->method('fetch')
            ->willThrowException(new \InvalidArgumentException());

        $this->storage->expects($this->never())
            ->method('save');

        $request = new Request(['oauth_token' => 'token', 'oauth_verifier' => 'code']);

        $resourceOwner->getAccessToken($request, 'http://redirect.to/');
    }

    public function testRefreshAccessToken()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner();
        $resourceOwner->refreshAccessToken('token');
    }

    public function testRevokeToken()
    {
        $this->expectException(AuthenticationException::class);

        $resourceOwner = $this->createResourceOwner();
        $resourceOwner->revokeToken('token');
    }

    public function testCsrfTokenIsAlwaysValidForOAuth1()
    {
        $resourceOwner = $this->createResourceOwner();

        $this->storage->expects($this->never())
            ->method('fetch');

        $this->assertTrue($resourceOwner->isCsrfTokenValid('valid_token'));
    }

    public function testCsrfTokenValid()
    {
        $resourceOwner = $this->createResourceOwner(['csrf' => true]);

        $this->storage->expects($this->never())
            ->method('fetch');

        $this->assertTrue($resourceOwner->isCsrfTokenValid('valid_token'));
    }

    public function testGetSetName()
    {
        $resourceOwner = $this->createResourceOwner();
        $this->assertEquals($this->prepareResourceOwnerName(), $resourceOwner->getName());
    }

    public function testCustomResponseClass()
    {
        $class = CustomUserResponse::class;
        $resourceOwner = $this->createResourceOwner(
            ['user_response_class' => $class],
            [],
            [
                $this->createMockResponse($this->userResponse),
            ]
        );

        /** @var CustomUserResponse $userResponse */
        $userResponse = $resourceOwner->getUserInformation(['oauth_token' => 'token', 'oauth_token_secret' => 'secret']);

        $this->assertInstanceOf($class, $userResponse);
        $this->assertEquals('foo666', $userResponse->getUsername());
        $this->assertEquals('foo', $userResponse->getNickname());
        $this->assertEquals('token', $userResponse->getAccessToken());
        $this->assertNull($userResponse->getRefreshToken());
        $this->assertNull($userResponse->getExpiresIn());
    }
}
