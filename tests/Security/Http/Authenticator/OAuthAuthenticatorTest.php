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
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapInterface;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomOAuthToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUser;
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
        $serviceLocator->expects($this->exactly(2))
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

    public function testRecreateToken()
    {
        $authenticator = new OAuthAuthenticator(
            $this->getHttpUtilsMock(),
            $this->getOAuthAwareUserProviderMock(),
            $this->getResourceOwnerMap(),
            ['/a', '/b'],
            $this->getAuthenticationSuccessHandlerMock(),
            $this->getAuthenticationFailureHandlerMock(),
            $this->createMock(HttpKernelInterface::class),
            []
        );

        $token = new CustomOAuthToken([
            'refresh_token' => 'refresh token data',
            'expires' => 666,
            'oauth_token_secret' => 'oauth secret',
        ]);
        $this->assertFalse($token->isExpired());
        $user = $token->getUser();
        $token->setResourceOwnerName('keycloak');
        $token->setCreatedAt(10);
        $token->setAttribute('attr a', 'attr a');

        $newToken = $authenticator->recreateToken($token);

        $this->assertInstanceOf(CustomOAuthToken::class, $newToken);
        $this->assertNotSame($token, $newToken);
        $this->assertSame($user, $newToken->getUser());
        $this->assertEquals('keycloak', $newToken->getResourceOwnerName());
        $this->assertEquals('access_token_data', $newToken->getAccessToken());
        $this->assertEquals('refresh token data', $newToken->getRefreshToken());
        $this->assertEquals(10, $newToken->getCreatedAt());
        $this->assertEquals(666, $newToken->getExpiresIn());
        $this->assertEquals('oauth secret', $newToken->getTokenSecret());
        $this->assertTrue($newToken->hasAttribute('attr a'));
        $this->assertEquals('attr a', $newToken->getAttribute('attr a'));
        $this->assertFalse($newToken->hasAttribute('non exists attr'));
    }

    public function testRefreshTokenExpiredAndNotContainsRefreshToken()
    {
        $resourceOwnerName = 'keycloak';

        $resourceOwnerMock = $this->getResourceOwnerMock();
        $resourceOwnerMock->expects($this->never())
            ->method('getUserInformation');

        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('getResourceOwnerByName')
            ->willReturn($resourceOwnerMock);

        $authenticator = new OAuthAuthenticator(
            $this->getHttpUtilsMock(),
            $this->getOAuthAwareUserProviderMock(),
            $resourceOwnerMapMock,
            ['/a', '/b'],
            $this->getAuthenticationSuccessHandlerMock(),
            $this->getAuthenticationFailureHandlerMock(),
            $this->createMock(HttpKernelInterface::class),
            []
        );

        $token = new CustomOAuthToken([
            'expires' => 666,
        ]);
        $token->setResourceOwnerName($resourceOwnerName);
        $token->setCreatedAt(10); // expire it

        $newToken = $authenticator->refreshToken($token);
        $this->assertSame($newToken, $token, 'Token missing refresh token data will not be refreshed if it already contains an user');
    }

    public function testRefreshTokenExpired()
    {
        $resourceOwnerName = 'keycloak';

        $userProviderMock = $this->getOAuthAwareUserProviderMock();
        $userProviderMock->expects($this->once())
            ->method('loadUserByOAuthUserResponse')
            ->willReturn($this->createUser());

        $resourceOwnerMock = $this->getResourceOwnerMock();
        $resourceOwnerMock->expects($this->once())
            ->method('refreshAccessToken')
            ->willReturn([
                'access_token' => 'access_token',
                'refresh_token' => 'refresh_token',
                'expires_in' => '666',
                'oauth_token_secret' => 'secret',
            ]);
        $resourceOwnerMock->expects($this->once())
            ->method('getUserInformation')
            ->willReturn($this->getUserResponseMock());

        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('getResourceOwnerByName')->willReturn($resourceOwnerMock);

        $authenticator = new OAuthAuthenticator(
            $this->getHttpUtilsMock(),
            $userProviderMock,
            $resourceOwnerMapMock,
            ['/a', '/b'],
            $this->getAuthenticationSuccessHandlerMock(),
            $this->getAuthenticationFailureHandlerMock(),
            $this->createMock(HttpKernelInterface::class),
            []
        );

        $token = new CustomOAuthToken([
            'expires' => 666,
            'refresh_token' => 'refresh token data',
        ]);
        $token->setResourceOwnerName($resourceOwnerName);
        $token->setCreatedAt(10); // expire it
        $user = $token->getUser();

        $token->setAttribute('non_persistent_key', 'some non persistent value');
        $token->setAttribute('persistent_key', 'some persistent value');

        $newToken = $authenticator->refreshToken($token);
        $this->assertNotSame($newToken, $token);
        $this->assertNotSame($newToken->getUser(), $user); // in real live may be rather the same

        $this->assertFalse($newToken->hasAttribute('non_persistent_key'));
        $this->assertTrue($newToken->hasAttribute('persistent_key'));
        $this->assertEquals('some persistent value', $newToken->getAttribute('persistent_key'));
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
     * @return ResourceOwnerMapInterface&MockObject
     */
    private function getResourceOwnerMapMock(): ResourceOwnerMapInterface
    {
        return $this->createMock(ResourceOwnerMapInterface::class);
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
