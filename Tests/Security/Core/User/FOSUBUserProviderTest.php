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

use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider,
    HWI\Bundle\OAuthBundle\Tests\Fixtures\User;

class FOSUBUserProviderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!interface_exists('FOS\UserBundle\Model\UserManagerInterface')) {
            $this->markTestSkipped('FOSUserBundle is not available');
        }
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No property defined for entity for resource owner 'not_configured'.
     */
    public function testLoadUserByOAuthuserResponseThrowsExceptionWhenNoPropertyIsConfigured()
    {
        $provider = $this->createFOSUBUserProvider();
        $provider->loadUserByOAuthUserResponse($this->createUserResponseMock(null, 'not_configured'));
    }

    /**
     * @expectedException \HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException
     * @expectedExceptionMessage User 'asm89' not found.
     */
    public function testLoadUserByOAuthuserResponseThrowsExceptionWhenUserIsNull()
    {
        $userResponseMock = $this->createUserResponseMock('asm89', 'github');

        $provider = $this->createFOSUBUserProvider(null);

        $provider->loadUserByOAuthUserResponse($userResponseMock);
    }

    public function testLoadUserByOAuthuserResponse()
    {
        $userResponseMock = $this->createUserResponseMock('asm89', 'github');

        $user = new User();
        $provider = $this->createFOSUBUserProvider($user);

        $loadedUser = $provider->loadUserByOAuthUserResponse($userResponseMock);

        $this->assertEquals($user, $loadedUser);
    }

    public function testConnectUser()
    {
        $user = new User();

        $userResponseMock = $this->createUserResponseMock('asm89', 'github');
        $provider = $this->createFOSUBUserProvider(false, $user);

        $provider->connect($user, $userResponseMock);

        $this->assertEquals('asm89', $user->getGithubId());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Class 'HWI\Bundle\OAuthBundle\Tests\Fixtures\User' should have a method 'setGoogleId'.
     */
    public function testConnectUserWithNoSetterThrowsException()
    {
        $user = new User();

        $userResponseMock = $this->createUserResponseMock(null, 'google');
        $provider = $this->createFOSUBUserProvider();

        $provider->connect($user, $userResponseMock);
    }

    protected function createFOSUBUserProvider($user = false, $updateUser = false)
    {
        $properties = array('github' => 'githubId', 'google' => 'googleId');

        $userManagerMock = $this->getMockBuilder('FOS\UserBundle\Model\UserManagerInterface')
            ->getMock();

        if (false !== $user) {
            $userManagerMock->expects($this->once())
                ->method('findUserBy')
                ->with(array('githubId' => 'asm89'))
                ->will($this->returnValue($user));
        }

        if (false !== $updateUser) {
            $userManagerMock->expects($this->once())
                ->method('updateUser')
                ->with($updateUser);
        }

        return new FOSUBUserProvider($userManagerMock, $properties);
    }

    protected function createResourceOwnerMock($resourceOwnerName = null)
    {
        $resourceOwnerMock = $this->getMockBuilder('HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface')
            ->getMock();

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
        $responseMock = $this->getMockBuilder('HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface')
            ->getMock();

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
