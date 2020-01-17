<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
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
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class FOSUBUserProviderTest extends TestCase
{
    protected function setUp(): void
    {
        if (!interface_exists('FOS\UserBundle\Model\UserManagerInterface')) {
            $this->markTestSkipped('FOSUserBundle is not available.');
        }

        if (!class_exists('Symfony\Component\PropertyAccess\PropertyAccess')) {
            $this->markTestSkipped('Symfony PropertyAccess component is not available.');
        }
    }

    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenNoPropertyIsConfigured()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No property defined for entity for resource owner \'not_configured\'.');

        $provider = $this->createFOSUBUserProvider();
        $provider->loadUserByOAuthUserResponse($this->createUserResponseMock(null, 'not_configured'));
    }

    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenUserIsNull()
    {
        $this->expectException(\HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException::class);
        $this->expectExceptionMessage('User \'asm89\' not found.');

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

    public function testConnectUserWithNoSetterThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not determine access type for property "facebookId".');

        $user = new FOSUser();

        $userResponseMock = $this->createUserResponseMock(null, 'facebook');
        $provider = $this->createFOSUBUserProvider();

        $provider->connect($user, $userResponseMock);
    }

    public function testRefreshUserThrowsExceptionWhenUserIsNull()
    {
        $userManagerMock = $this->createMock(UserManagerInterface::class);
        $userManagerMock->expects($this->once())
            ->method('findUserBy')
            ->willReturn(null);

        $provider = new FOSUBUserProvider($userManagerMock, []);

        try {
            $provider->refreshUser(new FOSUser());

            $this->fail('Failed asserting exception');
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf(UsernameNotFoundException::class, $e);
            $this->assertSame('User with ID "1" could not be reloaded.', $e->getMessage());
            $this->assertSame('foo', $e->getUsername());
        }
    }

    protected function createFOSUBUserProvider($user = null, $updateUser = null)
    {
        $userManagerMock = $this->createMock(UserManagerInterface::class);

        if (null !== $user) {
            $userManagerMock->expects($this->once())
                ->method('findUserBy')
                ->with(['githubId' => 'asm89'])
                ->willReturn($user);
        }

        if (null !== $updateUser) {
            $userManagerMock->expects($this->once())
                ->method('updateUser')
                ->with($updateUser);
        }

        return new FOSUBUserProvider($userManagerMock, ['github' => 'githubId', 'google' => 'googleId', 'facebook' => 'facebookId']);
    }

    protected function createResourceOwnerMock($resourceOwnerName = null)
    {
        $resourceOwnerMock = $this->createMock(ResourceOwnerInterface::class);

        if (null !== $resourceOwnerName) {
            $resourceOwnerMock
                ->expects($this->once())
                ->method('getName')
                ->willReturn($resourceOwnerName);
        }

        return $resourceOwnerMock;
    }

    protected function createUserResponseMock($username = null, $resourceOwnerName = null)
    {
        $responseMock = $this->createMock(UserResponseInterface::class);

        if (null !== $resourceOwnerName) {
            $responseMock
                ->expects($this->once())
                ->method('getResourceOwner')
                ->willReturn($this->createResourceOwnerMock($resourceOwnerName));
        }

        if (null !== $username) {
            $responseMock
                ->expects($this->once())
                ->method('getUsername')
                ->willReturn($username);
        }

        return $responseMock;
    }
}
