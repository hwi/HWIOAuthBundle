<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Security\Core\Authentication\Provider;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Provider\OAuthProvider;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\OAuthAwareException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthProviderTest extends TestCase
{
    public function testSupportsOAuthToken()
    {
        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('hasResourceOwnerByName')
            ->with($this->equalTo('owner'))
            ->willReturn(true);

        $oauthProvider = new OAuthProvider($this->getOAuthAwareUserProviderMock(), $resourceOwnerMapMock, $this->getUserCheckerMock(), $this->getTokenStorageMock());

        $token = new OAuthToken('');
        $token->setResourceOwnerName('owner');

        $this->assertTrue($oauthProvider->supports($token));
    }

    public function testAuthenticatesToken()
    {
        $expectedToken = [
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666',
            'oauth_token_secret' => 'secret',
        ];

        $oauthTokenMock = $this->getOAuthTokenMock();
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getResourceOwnerName')
            ->willReturn('github');
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getRawToken')
            ->willReturn($expectedToken);
        $oauthTokenMock->expects($this->once())
            ->method('getRefreshToken')
            ->willReturn($expectedToken['refresh_token']);

        $resourceOwnerMock = $this->getResourceOwnerMock();
        $resourceOwnerMock->expects($this->once())
            ->method('getUserInformation')
            ->with($expectedToken)
            ->willReturn($this->getUserResponseMock());
        $resourceOwnerMock->expects($this->once())
            ->method('getName')
            ->willReturn('github');

        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('getResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->willReturn($resourceOwnerMock);
        $resourceOwnerMapMock->expects($this->once())
            ->method('hasResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->willReturn(true);

        $userMock = $this->getUserMock();
        $userMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(['ROLE_TEST']);

        $userProviderMock = $this->getOAuthAwareUserProviderMock();
        $userProviderMock->expects($this->once())
            ->method('loadUserByOAuthUserResponse')
            ->willReturn($userMock);

        $userCheckerMock = $this->getUserCheckerMock();
        $userCheckerMock->expects($this->once())
            ->method('checkPostAuth')
            ->with($userMock);

        $tokenStorageMock = $this->getTokenStorageMock();

        $oauthProvider = new OAuthProvider($userProviderMock, $resourceOwnerMapMock, $userCheckerMock, $tokenStorageMock);

        $token = $oauthProvider->authenticate($oauthTokenMock);
        $this->assertTrue($token->isAuthenticated());
        $this->assertInstanceOf(OAuthToken::class, $token);

        $this->assertEquals($expectedToken, $token->getRawToken());
        $this->assertEquals($expectedToken['access_token'], $token->getAccessToken());
        $this->assertEquals($expectedToken['refresh_token'], $token->getRefreshToken());
        $this->assertEquals($expectedToken['expires_in'], $token->getExpiresIn());
        $this->assertEquals($expectedToken['oauth_token_secret'], $token->getTokenSecret());
        $this->assertEquals($userMock, $token->getUser());
        $this->assertEquals('github', $token->getResourceOwnerName());

        $roles = $this->getRoleNames($token);
        $this->assertCount(1, $roles);
        $this->assertEquals('ROLE_TEST', $roles[0]);
    }

    public function testOAuthAwareExceptionGetsInfo()
    {
        $expectedToken = [
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666',
            'oauth_token_secret' => 'secret',
        ];

        $oauthTokenMock = $this->getOAuthTokenMock();
        $oauthTokenMock->expects($this->exactly(3))
            ->method('getResourceOwnerName')
            ->willReturn('github');
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getRawToken')
            ->willReturn($expectedToken);
        $oauthTokenMock->expects($this->once())
            ->method('getAccessToken')
            ->willReturn($expectedToken['access_token']);
        $oauthTokenMock->expects($this->once())
            ->method('getRefreshToken')
            ->willReturn($expectedToken['refresh_token']);
        $oauthTokenMock->expects($this->once())
            ->method('getExpiresIn')
            ->willReturn($expectedToken['expires_in']);
        $oauthTokenMock->expects($this->once())
            ->method('getTokenSecret')
            ->willReturn($expectedToken['oauth_token_secret']);

        $resourceOwnerMock = $this->getResourceOwnerMock();
        $resourceOwnerMock->expects($this->once())
            ->method('getUserInformation')
            ->with($expectedToken)
            ->willReturn($this->getUserResponseMock());

        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('getResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->willReturn($resourceOwnerMock);
        $resourceOwnerMapMock->expects($this->once())
            ->method('hasResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->willReturn(true);

        $userProviderMock = $this->getOAuthAwareUserProviderMock();
        $userProviderMock->expects($this->once())
            ->method('loadUserByOAuthUserResponse')
            ->will($this->throwException(new OAuthAwareException()));

        $userCheckerMock = $this->getUserCheckerMock();

        $tokenStorageMock = $this->getTokenStorageMock();

        $oauthProvider = new OAuthProvider($userProviderMock, $resourceOwnerMapMock, $userCheckerMock, $tokenStorageMock);

        try {
            $oauthProvider->authenticate($oauthTokenMock);

            $this->assertTrue(false, 'Exception was not thrown.');
        } catch (OAuthAwareException $e) {
            $this->assertTrue(true, 'Exception was thrown.');
            $this->assertInstanceOf(OAuthAwareExceptionInterface::class, $e);
            $this->assertEquals('github', $e->getResourceOwnerName());
            $this->assertEquals($expectedToken['access_token'], $e->getAccessToken());
            $this->assertEquals($expectedToken['refresh_token'], $e->getRefreshToken());
            $this->assertEquals($expectedToken['expires_in'], $e->getExpiresIn());
            $this->assertEquals($expectedToken['oauth_token_secret'], $e->getTokenSecret());
            $this->assertEquals($expectedToken, $e->getRawToken());
        }
    }

    public function testTokenRefreshesWhenExpired()
    {
        $expiredToken = [
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666',
            'oauth_token_secret' => 'secret',
        ];

        $refreshedToken = [
            'access_token' => 'access_token_new',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666_new',
            'oauth_token_secret' => 'secret_new',
        ];

        $oauthTokenMock = $this->getOAuthTokenMock();
        $oauthTokenMock->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getResourceOwnerName')
            ->willReturn('github');
        $oauthTokenMock->expects($this->exactly(3))
            ->method('getRefreshToken')
            ->willReturn($expiredToken['refresh_token']);

        $resourceOwnerMock = $this->getResourceOwnerMock();
        $resourceOwnerMock->expects($this->once())
            ->method('refreshAccessToken')
            ->with($expiredToken['refresh_token'])
            ->willReturn($refreshedToken);
        $resourceOwnerMock->expects($this->once())
            ->method('getUserInformation')
            ->with($refreshedToken)
            ->willReturn($this->getUserResponseMock());
        $resourceOwnerMock->expects($this->once())
            ->method('getName')
            ->willReturn('github');

        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('getResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->willReturn($resourceOwnerMock);
        $resourceOwnerMapMock->expects($this->once())
            ->method('hasResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->willReturn(true);

        $userMock = $this->getUserMock();
        $userMock->expects($this->once())
            ->method('getRoles')
            ->willReturn(['ROLE_TEST']);

        $userProviderMock = $this->getOAuthAwareUserProviderMock();
        $userProviderMock->expects($this->once())
            ->method('loadUserByOAuthUserResponse')
            ->willReturn($userMock);

        $userCheckerMock = $this->getUserCheckerMock();
        $userCheckerMock->expects($this->once())
            ->method('checkPostAuth')
            ->with($userMock);

        $tokenStorageMock = $this->getTokenStorageMock();

        $oauthProvider = new OAuthProvider($userProviderMock, $resourceOwnerMapMock, $userCheckerMock, $tokenStorageMock);

        $token = $oauthProvider->authenticate($oauthTokenMock);
        $this->assertTrue($token->isAuthenticated());
        $this->assertInstanceOf(OAuthToken::class, $token);

        $this->assertEquals($refreshedToken, $token->getRawToken());
        $this->assertEquals($refreshedToken['access_token'], $token->getAccessToken());
        $this->assertEquals($refreshedToken['refresh_token'], $token->getRefreshToken());
        $this->assertEquals($refreshedToken['expires_in'], $token->getExpiresIn());
        $this->assertEquals($refreshedToken['oauth_token_secret'], $token->getTokenSecret());
        $this->assertEquals($userMock, $token->getUser());
        $this->assertEquals('github', $token->getResourceOwnerName());

        $roles = $this->getRoleNames($token);
        $this->assertCount(1, $roles);
        $this->assertEquals('ROLE_TEST', $roles[0]);
    }

    protected function getOAuthAwareUserProviderMock()
    {
        return $this->createMock(OAuthAwareUserProviderInterface::class);
    }

    protected function getResourceOwnerMapMock()
    {
        return $this->createMock(ResourceOwnerMap::class);
    }

    protected function getOAuthTokenMock()
    {
        return $this->createMock(OAuthToken::class);
    }

    protected function getResourceOwnerMock()
    {
        return $this->createMock(ResourceOwnerInterface::class);
    }

    protected function getUserResponseMock()
    {
        return $this->createMock(UserResponseInterface::class);
    }

    protected function getUserCheckerMock()
    {
        return $this->createMock(UserCheckerInterface::class);
    }

    protected function getUserMock()
    {
        return $this->createMock(UserInterface::class);
    }

    protected function getTokenStorageMock()
    {
        return $this->createMock(TokenStorageInterface::class);
    }

    /**
     * Symfony < 4.3 BC layer.
     *
     * @param TokenInterface $token
     *
     * @return array
     */
    private function getRoleNames(TokenInterface $token)
    {
        if (method_exists($token, 'getRoleNames')) {
            $roles = $token->getRoleNames();
        } else {
            $roles = array_map(function (Role $role) {
                return $role->getRole();
            }, $token->getRoles());
        }

        return $roles;
    }
}
