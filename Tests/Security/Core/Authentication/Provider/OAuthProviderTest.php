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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken,
    HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface,
    HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface,
    HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;

use HWI\Bundle\OAuthBundle\Tests\Fixtures\OAuthAwareException;

class OAuthProviderTest extends \PHPUnit_Framework_Testcase
{
    protected function getOAuthAwareUserProviderMock()
    {
        return $this->getMockBuilder('\HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface')
            ->getMock();
    }

    protected function getResourceOwnerMapMock()
    {
        return $this->getMockBuilder('\HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap')
            ->disableOriginalConstructor()->getMock();
    }

    protected function getOAuthTokenMock()
    {
        return $this->getMockBuilder('\HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken')
            ->disableOriginalConstructor()->getMock();
    }

    protected function getResourceOwnerMock()
    {
        return $this->getMockBuilder('\HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface')
            ->getMock();
    }

    protected function getUserResponseMock()
    {
        return $this->getMockBuilder('\HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface')
            ->getMock();
    }

    protected function getUserMock()
    {
        return $this->getMockBuilder('\Symfony\Component\Security\Core\User\UserInterface')
            ->getMock();
    }

    public function testSupportsOAuthToken()
    {
        $oauthProvider = new OAuthProvider($this->getOAuthAwareUserProviderMock(), $this->getResourceOwnerMapMock());
        $this->assertTrue($oauthProvider->supports(new OAuthToken('')));
    }

    public function testAuthenticatesToken()
    {
        $oauthTokenMock = $this->getOAuthTokenMock();
        $oauthTokenMock->expects($this->once())
            ->method('getResourceOwnerName')
            ->will($this->returnValue('github'));
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getCredentials')
            ->will($this->returnValue('creds'));

        $resourceOwnerMock = $this->getResourceOwnerMock();
        $resourceOwnerMock->expects($this->once())
            ->method('getUserInformation')
            ->with($this->equalTo('creds'))
            ->will($this->returnValue($this->getUserResponseMock()));

        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('getResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->will($this->returnValue($resourceOwnerMock));

        $userMock = $this->getUserMock();
        $userMock->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(array('ROLE_TEST')));

        $userProviderMock = $this->getOAuthAwareUserProviderMock();
        $userProviderMock->expects($this->once())
            ->method('loadUserByOAuthUserResponse')
            ->will($this->returnValue($userMock));

        $oauthProvider = new OAuthProvider($userProviderMock, $resourceOwnerMapMock);

        $token = $oauthProvider->authenticate($oauthTokenMock);
        $this->assertTrue($token->isAuthenticated());
        $this->assertInstanceof('HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken', $token);
        $this->assertEquals('creds', $token->getCredentials());
        $this->assertEquals($userMock, $token->getUser());

        $roles = $token->getRoles();
        $this->assertCount(1, $roles);
        $this->assertEquals('ROLE_TEST', $roles[0]->getRole());
    }

    public function testOAuthAwareExceptionGetsInfo()
    {
        $oauthTokenMock = $this->getOAuthTokenMock();
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getResourceOwnerName')
            ->will($this->returnValue('github'));
        $oauthTokenMock->expects($this->exactly(2))
            ->method('getCredentials')
            ->will($this->returnValue('creds'));

        $resourceOwnerMock = $this->getResourceOwnerMock();
        $resourceOwnerMock->expects($this->once())
            ->method('getUserInformation')
            ->with($this->equalTo('creds'))
            ->will($this->returnValue($this->getUserResponseMock()));

        $resourceOwnerMapMock = $this->getResourceOwnerMapMock();
        $resourceOwnerMapMock->expects($this->once())
            ->method('getResourceOwnerByName')
            ->with($this->equalTo('github'))
            ->will($this->returnValue($resourceOwnerMock));

        $userProviderMock = $this->getOAuthAwareUserProviderMock();
        $userProviderMock->expects($this->once())
            ->method('loadUserByOAuthUserResponse')
             ->will($this->throwException(new OAuthAwareException));

        $oauthProvider = new OAuthProvider($userProviderMock, $resourceOwnerMapMock);

        try {
            $token = $oauthProvider->authenticate($oauthTokenMock);
            $this->assertTrue(false, "Exception was not thrown.");
        } catch(OAuthAwareException $e) {
            $this->assertTrue(true, "Exception was thrown.");
            $this->assertInstanceOf('HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface', $e);
            $this->assertEquals('github', $e->getResourceOwnerName());
            $this->assertEquals('creds', $e->getAccessToken());
        }
    }
}
