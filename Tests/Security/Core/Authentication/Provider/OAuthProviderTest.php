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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Provider\OAuthProvider;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\OAuthAwareException;

class OAuthProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testSupportsOAuthToken()
    {
        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('hasResourceOwnerByName')
            ->with($this->equalTo('owner'))
            ->will($this->returnValue(true));

        $oauthProvider = new OAuthProvider($this->getOAuthAwareUserProviderMock(), $resourceOwnerMapMock, $this->getUserCheckerMock());

        $token = new OAuthToken('');
        $token->setResourceOwnerName('owner');

        $this->assertTrue($oauthProvider->supports($token));
    }

    public function testAuthenticatesToken()
    {
        $expectedToken = array(
            'access_token'  => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in'    => '666',
            'oauth_token_secret' => 'secret'
        );

        $oauthTokenMock = $this->getOAuthTokenMock();
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getResourceOwnerName')
            ->will($this->returnValue('github'));
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getRawToken')
            ->will($this->returnValue($expectedToken));

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

        $oauthProvider = new OAuthProvider($userProviderMock, $resourceOwnerMapMock, $userCheckerMock);

        $token = $oauthProvider->authenticate($oauthTokenMock);
        $this->assertTrue($token->isAuthenticated());
        $this->assertInstanceof('HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken', $token);
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
            'access_token'  => 'access_token',
            'refresh_token' => 'refresh_token',
            'expires_in'    => '666',
            'oauth_token_secret' => 'secret'
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
             ->will($this->throwException(new OAuthAwareException));

        $userCheckerMock = $this->getUserCheckerMock();

        $oauthProvider = new OAuthProvider($userProviderMock, $resourceOwnerMapMock, $userCheckerMock);

        try {
            $oauthProvider->authenticate($oauthTokenMock);

            $this->assertTrue(false, "Exception was not thrown.");
        } catch (OAuthAwareException $e) {
            $this->assertTrue(true, "Exception was thrown.");
            $this->assertInstanceOf('HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface', $e);
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
        return $this->getMockBuilder('\HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getResourceOwnerMapMock()
    {
        return $this->getMockBuilder('\HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getOAuthTokenMock()
    {
        return $this->getMockBuilder('\HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getResourceOwnerMock()
    {
        return $this->getMockBuilder('\HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getUserResponseMock()
    {
        return $this->getMockBuilder('\HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getUserCheckerMock()
    {
        return $this->getMockBuilder('\Symfony\Component\Security\Core\User\UserCheckerInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getUserMock()
    {
        return $this->getMockBuilder('\Symfony\Component\Security\Core\User\UserInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
