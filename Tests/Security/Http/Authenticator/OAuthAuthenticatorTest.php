<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Security\Http\Authenticator;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\AbstractOAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Http\Authenticator\OAuthAuthenticator;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\HttpUtils;

/**
 * @author Vadim Borodavko <vadim.borodavko@gmail.com>
 */
final class OAuthAuthenticatorTest extends TestCase
{
    protected function setUp(): void
    {
        // Symfony < 5.1 BC layer.
        if (!interface_exists(AuthenticatorInterface::class)) {
            $this->markTestSkipped('Symfony new Authenticator-based security system is not available.');
        }
    }

    public function testSupports(): void
    {
        $httpUtilsMock = $this->getHttpUtilsMock();
        $request = Request::create('/b');

        $httpUtilsMock
            ->method('checkRequestPath')
            ->withConsecutive(
                [$request, '/a'],
                [$request, '/b']
            )
            ->willReturnOnConsecutiveCalls(
                false,
                true
            );

        $authenticator = new OAuthAuthenticator(
            $httpUtilsMock,
            $this->getOAuthAwareUserProviderMock(),
            $this->getResourceOwnerMap(),
            ['/a', '/b'],
            $this->getAuthenticationSuccessHandlerMock(),
            $this->getAuthenticationFailureHandlerMock(),
            $this->createMock(HttpKernelInterface::class),
            []
        );

        $this->assertTrue($authenticator->supports($request));
    }

    public function testAuthenticate(): void
    {
        $httpUtilsMock = $this->getHttpUtilsMock();
        $userProviderMock = $this->getOAuthAwareUserProviderMock();
        $resourceOwnerMock = $this->getResourceOwnerMock();
        $checkPath = '/oauth/login_check';
        $request = Request::create($checkPath);
        $checkUri = 'http://localhost/oauth/login_check';
        $accessToken = [
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666',
            'oauth_token_secret' => 'secret',
        ];
        $userResponseMock = $this->getUserResponseMock();
        $user = $this->createUser();
        $resourceOwnerName = 'github';

        $httpUtilsMock->expects($this->once())
            ->method('checkRequestPath')
            ->with($request, $checkPath)
            ->willReturn(true);

        $serviceLocator = $this->createMock(ServiceLocator::class);
        $serviceLocator->expects($this->once())
            ->method('get')
            ->with($resourceOwnerName)
            ->willReturn($resourceOwnerMock);

        $resourceOwnerMap = $this->getResourceOwnerMap(
            [$resourceOwnerName => $checkPath],
            $httpUtilsMock,
            $serviceLocator
        );

        $resourceOwnerMock->expects($this->once())
            ->method('handles')
            ->with($request)
            ->willReturn(true);

        $resourceOwnerMock->expects($this->once())
            ->method('isCsrfTokenValid')
            ->with(null);

        $httpUtilsMock->expects($this->once())
            ->method('createRequest')
            ->with($request, $checkPath)
            ->willReturn(Request::create($checkUri));

        $resourceOwnerMock->expects($this->once())
            ->method('getAccessToken')
            ->with($request, $checkUri)
            ->willReturn($accessToken);

        $resourceOwnerMock->expects($this->once())
            ->method('getUserInformation')
            ->with($accessToken)
            ->willReturn($userResponseMock);

        $userProviderMock->expects($this->once())
            ->method('loadUserByOAuthUserResponse')
            ->with($userResponseMock)
            ->willReturn($user);

        $resourceOwnerMock->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn($resourceOwnerName);

        $authenticator = new OAuthAuthenticator(
            $httpUtilsMock,
            $userProviderMock,
            $resourceOwnerMap,
            [],
            $this->getAuthenticationSuccessHandlerMock(),
            $this->getAuthenticationFailureHandlerMock(),
            $this->createMock(HttpKernelInterface::class),
            []
        );

        $passport = $authenticator->authenticate($request);
        $this->assertInstanceOf(SelfValidatingPassport::class, $passport);
        $this->assertEquals($user, $passport->getUser());

        /** @var AbstractOAuthToken $token */
        $token = $authenticator->createAuthenticatedToken($passport, 'main');
        $this->assertInstanceOf(OAuthToken::class, $token);
        $this->assertEquals($resourceOwnerName, $token->getResourceOwnerName());
        $this->assertEquals($user, $token->getUser());
        $this->assertEquals('refresh_token', $token->getRefreshToken());

        // required for compatibility with Symfony 5.4
        if (method_exists($token, 'isAuthenticated')) {
            $this->assertTrue($token->isAuthenticated());
        }
    }

    public function testOnAuthenticationSuccess(): void
    {
        $request = Request::create('/auth');
        $token = $this->getOAuthTokenMock();
        $response = new Response();

        $successHandlerMock = $this->getAuthenticationSuccessHandlerMock();

        $successHandlerMock->expects($this->once())
            ->method('onAuthenticationSuccess')
            ->with($request, $token)
            ->willReturn($response);

        $authenticator = new OAuthAuthenticator(
            $this->getHttpUtilsMock(),
            $this->getOAuthAwareUserProviderMock(),
            $this->getResourceOwnerMap(),
            [],
            $successHandlerMock,
            $this->getAuthenticationFailureHandlerMock(),
            $this->createMock(HttpKernelInterface::class),
            []
        );

        $this->assertSame($response, $authenticator->onAuthenticationSuccess($request, $token, 'main'));
    }

    public function testOnAuthenticationFailure(): void
    {
        $request = Request::create('/auth');
        $exception = new AuthenticationException();
        $response = new Response();

        $failureHandlerMock = $this->getAuthenticationFailureHandlerMock();

        $failureHandlerMock->expects($this->once())
            ->method('onAuthenticationFailure')
            ->with($request, $exception)
            ->willReturn($response);

        $authenticator = new OAuthAuthenticator(
            $this->getHttpUtilsMock(),
            $this->getOAuthAwareUserProviderMock(),
            $this->getResourceOwnerMap(),
            [],
            $this->getAuthenticationSuccessHandlerMock(),
            $failureHandlerMock,
            $this->createMock(HttpKernelInterface::class),
            []
        );

        $this->assertSame($response, $authenticator->onAuthenticationFailure($request, $exception));
    }

    /**
     * @return HttpUtils&MockObject
     */
    private function getHttpUtilsMock(): HttpUtils
    {
        return $this->createMock(HttpUtils::class);
    }

    /**
     * @return OAuthAwareUserProviderInterface&MockObject
     */
    private function getOAuthAwareUserProviderMock(): OAuthAwareUserProviderInterface
    {
        return $this->createMock(OAuthAwareUserProviderInterface::class);
    }

    /**
     * @return AuthenticationSuccessHandlerInterface&MockObject
     */
    private function getAuthenticationSuccessHandlerMock(): AuthenticationSuccessHandlerInterface
    {
        return $this->createMock(AuthenticationSuccessHandlerInterface::class);
    }

    /**
     * @return AuthenticationFailureHandlerInterface&MockObject
     */
    private function getAuthenticationFailureHandlerMock(): AuthenticationFailureHandlerInterface
    {
        return $this->createMock(AuthenticationFailureHandlerInterface::class);
    }

    /**
     * @return ResourceOwnerInterface&MockObject
     */
    private function getResourceOwnerMock(): ResourceOwnerInterface
    {
        return $this->createMock(ResourceOwnerInterface::class);
    }

    /**
     * @return UserResponseInterface&MockObject
     */
    private function getUserResponseMock(): UserResponseInterface
    {
        return $this->createMock(UserResponseInterface::class);
    }

    private function createUser(): UserInterface
    {
        if (class_exists(User::class)) {
            // @phpstan-ignore-next-line Symfony < 5.4 BC layer
            return new User('asm89', 'foo', ['ROLE_USER']);
        }

        return new InMemoryUser('asm89', 'foo', ['ROLE_USER']);
    }

    /**
     * @return OAuthToken&MockObject
     */
    private function getOAuthTokenMock(): OAuthToken
    {
        return $this->createMock(OAuthToken::class);
    }

    private function getResourceOwnerMap(
        array $resources = [],
        $httpUtils = null,
        $serviceLocator = null
    ): ResourceOwnerMap {
        return new ResourceOwnerMap(
            $httpUtils ?: $this->createMock(HttpUtils::class),
            $resources,
            $resources,
            $serviceLocator ?: $this->createMock(ServiceLocator::class)
        );
    }
}
