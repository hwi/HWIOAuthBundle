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

use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\FOSUser;
use PHPUnit\Framework\TestCase;

class FOSUBUserProviderTest extends TestCase
{
    public function setUp()
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
     * @expectedExceptionMessage Could not determine access type for property "googleId".
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
        $userManagerMock = $this->getMockBuilder(UserManagerInterface::class)
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

        return new FOSUBUserProvider($userManagerMock, ['github' => 'githubId', 'google' => 'googleId']);
    }

    protected function createResourceOwnerMock($resourceOwnerName = null)
    {
        $resourceOwnerMock = $this->getMockBuilder(ResourceOwnerInterface::class)
            ->disableOriginalConstructor()
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
        $responseMock = $this->getMockBuilder(UserResponseInterface::class)
            ->disableOriginalConstructor()
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
