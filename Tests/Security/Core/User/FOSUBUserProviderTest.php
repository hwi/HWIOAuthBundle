<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Security\Core\User;

use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\FOSUser;

class FOSUBUserProviderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!interface_exists('FOS\UserBundle\Model\UserManagerInterface')) {
            $this->markTestSkipped('FOSUserBundle is not available.');
        }

        if (!class_exists('Symfony\Component\PropertyAccess\PropertyAccess')) {
            $this->markTestSkipped('Symfony PropertyAccess component is not available.');
        }
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No property defined for entity for resource owner 'not_configured'.
     */
    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenNoPropertyIsConfigured()
    {
        $provider = $this->createFOSUBUserProvider();
        $provider->loadUserByOAuthUserResponse($this->createUserResponseMock(null, 'not_configured'));
    }

    /**
     * @expectedException \HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException
     * @expectedExceptionMessage User 'asm89' not found.
     */
    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenUserIsNull()
    {
        $userResponseMock = $this->createUserResponseMock('asm89', 'github');

        $provider = $this->createFOSUBUserProvider();

        $provider->loadUserByOAuthUserResponse($userResponseMock);
    }

    public function testLoadUserByOAuthUserResponse()
    {
        $userResponseMock = $this->createUserResponseMock('asm89', 'github');

        $user = new FOSUser();
        $provider = $this->createFOSUBUserProvider($user);

        $loadedUser = $provider->loadUserByOAuthUserResponse($userResponseMock);

        $this->assertEquals($user, $loadedUser);
    }

    public function testConnectUser()
    {
        $user = new FOSUser();

        $userResponseMock = $this->createUserResponseMock('asm89', 'github');
        $provider = $this->createFOSUBUserProvider(null, $user);

        $provider->connect($user, $userResponseMock);

        $this->assertEquals('asm89', $user->getGithubId());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Class 'HWI\Bundle\OAuthBundle\Tests\Fixtures\FOSUser' must have defined setter method for property: 'googleId'.
     */
    public function testConnectUserWithNoSetterThrowsException()
    {
        $user = new FOSUser();

        $userResponseMock = $this->createUserResponseMock(null, 'google');
        $provider = $this->createFOSUBUserProvider();

        $provider->connect($user, $userResponseMock);
    }

    protected function createFOSUBUserProvider($user = null, $updateUser = null)
    {
        $properties = array('github' => 'githubId', 'google' => 'googleId');

        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')
            ->getMock();

        if (null !== $user) {
            $userManagerMock->expects($this->once())
                ->method('findUserBy')
                ->with(array('githubId' => 'asm89'))
                ->will($this->returnValue($user));
        }

        if (null !== $updateUser) {
            $userManagerMock->expects($this->once())
                ->method('updateUser')
                ->with($updateUser);
        }

        return new FOSUBUserProvider($userManagerMock, $properties);
    }

    protected function createResourceOwnerMock($resourceOwnerName = null)
    {
        $resourceOwnerMock = $this->getMock('HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface');

        if (null !== $resourceOwnerName) {
            $resourceOwnerMock
                ->expects($this->once())
                ->method('getName')
                ->will($this->returnValue($resourceOwnerName));
        }

        return $resourceOwnerMock;
    }

    protected function createUserResponseMock($username = null, $resourceOwnerName = null)
    {
        $responseMock = $this->getMock('HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface');

        if (null !== $resourceOwnerName) {
            $responseMock
                ->expects($this->once())
                ->method('getResourceOwner')
                ->will($this->returnValue($this->createResourceOwnerMock($resourceOwnerName)));
        }

        if (null !== $username) {
            $responseMock
                ->expects($this->once())
                ->method('getUsername')
                ->will($this->returnValue($username));
        }

        return $responseMock;
    }
}
