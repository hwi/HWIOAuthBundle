<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
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
            ->will($this->returnValue(true));

        $oauthProvider = new OAuthProvider($this->getOAuthAwareUserProviderMock(), $resourceOwnerMapMock, $this->getUserCheckerMock(), $this->getTokenStorageMock());

        $token = new OAuthToken('');
        $token->setResourceOwnerName('owner');

        $this->assertTrue($oauthProvider->supports($token));
    }

    public function testAuthenticatesToken()
    {
        $expectedToken = array(
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666',
            'oauth_token_secret' => 'secret',
        );

        $oauthTokenMock = $this->getOAuthTokenMock();
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getResourceOwnerName')
            ->will($this->returnValue('github'));
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getRawToken')
            ->will($this->returnValue($expectedToken));
        $oauthTokenMock->expects($this->once())
            ->method('getRefreshToken')
            ->willReturn($expectedToken['refresh_token']);

        $resourceOwnerMock = $this->getResourceOwnerMock();
        $resourceOwnerMock->expects($this->once())
            ->method('getUserInformation')
            ->with($expectedToken)
            ->will($this->returnValue($this->getUserResponseMock()));
        $resourceOwnerMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('github'));

        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('getResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->will($this->returnValue($resourceOwnerMock));
        $resourceOwnerMapMock->expects($this->once())
            ->method('hasResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->will($this->returnValue(true));

        $userMock = $this->getUserMock();
        $userMock->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(array('ROLE_TEST')));

        $userProviderMock = $this->getOAuthAwareUserProviderMock();
        $userProviderMock->expects($this->once())
            ->method('loadUserByOAuthUserResponse')
            ->will($this->returnValue($userMock));

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

        $roles = $token->getRoles();
        $this->assertCount(1, $roles);
        $this->assertEquals('ROLE_TEST', $roles[0]->getRole());
    }

    public function testOAuthAwareExceptionGetsInfo()
    {
        $expectedToken = array(
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666',
            'oauth_token_secret' => 'secret',
        );

        $oauthTokenMock = $this->getOAuthTokenMock();
        $oauthTokenMock->expects($this->exactly(3))
            ->method('getResourceOwnerName')
            ->will($this->returnValue('github'));
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getRawToken')
            ->will($this->returnValue($expectedToken));
        $oauthTokenMock->expects($this->once())
            ->method('getAccessToken')
            ->will($this->returnValue($expectedToken['access_token']));
        $oauthTokenMock->expects($this->once())
            ->method('getRefreshToken')
            ->will($this->returnValue($expectedToken['refresh_token']));
        $oauthTokenMock->expects($this->once())
            ->method('getExpiresIn')
            ->will($this->returnValue($expectedToken['expires_in']));
        $oauthTokenMock->expects($this->once())
            ->method('getTokenSecret')
            ->will($this->returnValue($expectedToken['oauth_token_secret']));

        $resourceOwnerMock = $this->getResourceOwnerMock();
        $resourceOwnerMock->expects($this->once())
            ->method('getUserInformation')
            ->with($expectedToken)
            ->will($this->returnValue($this->getUserResponseMock()));

        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('getResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->will($this->returnValue($resourceOwnerMock));
        $resourceOwnerMapMock->expects($this->once())
            ->method('hasResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->will($this->returnValue(true));

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

    protected function getOAuthAwareUserProviderMock()
    {
        return $this->getMockBuilder(OAuthAwareUserProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getResourceOwnerMapMock()
    {
        return $this->getMockBuilder(ResourceOwnerMap::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getOAuthTokenMock()
    {
        return $this->getMockBuilder(OAuthToken::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getResourceOwnerMock()
    {
        return $this->getMockBuilder(ResourceOwnerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getUserResponseMock()
    {
        return $this->getMockBuilder(UserResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getUserCheckerMock()
    {
        return $this->getMockBuilder(UserCheckerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getUserMock()
    {
        return $this->getMockBuilder(UserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getTokenStorageMock()
    {
        return $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testTokenRefreshesWhenExpired()
    {
        $expiredToken = array(
            'access_token' => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666',
            'oauth_token_secret' => 'secret',
        );

        $refreshedToken = array(
            'access_token' => 'access_token_new',
            'refresh_token' => 'refresh_token',
            'expires_in' => '666_new',
            'oauth_token_secret' => 'secret_new',
        );

        $oauthTokenMock = $this->getOAuthTokenMock();
        $oauthTokenMock->expects($this->once())
            ->method('isExpired')
            ->willReturn(true);
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getResourceOwnerName')
            ->will($this->returnValue('github'));
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
            ->will($this->returnValue($this->getUserResponseMock()));
        $resourceOwnerMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('github'));

        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('getResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->will($this->returnValue($resourceOwnerMock));
        $resourceOwnerMapMock->expects($this->once())
            ->method('hasResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->will($this->returnValue(true));

        $userMock = $this->getUserMock();
        $userMock->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(array('ROLE_TEST')));

        $userProviderMock = $this->getOAuthAwareUserProviderMock();
        $userProviderMock->expects($this->once())
            ->method('loadUserByOAuthUserResponse')
            ->will($this->returnValue($userMock));

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

        $roles = $token->getRoles();
        $this->assertCount(1, $roles);
        $this->assertEquals('ROLE_TEST', $roles[0]->getRole());
    }
}
