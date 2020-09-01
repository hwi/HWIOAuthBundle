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

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class EntityUserProviderTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists('Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped('The Doctrine ORM is not available');
        }
    }

    public function testLoadUserByUsernameThrowsExceptionWhenUserIsNull()
    {
        $provider = $this->createEntityUserProvider();

        try {
            $provider->loadUserByUsername('asm89');

            $this->fail('Failed asserting exception');
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf(UsernameNotFoundException::class, $e);
            $this->assertSame("User 'asm89' not found.", $e->getMessage());
            $this->assertSame('asm89', $e->getUsername());
        }
    }

    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenNoPropertyIsConfigured()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No property defined for entity for resource owner \'not_configured\'.');

        $provider = $this->createEntityUserProvider();
        $provider->loadUserByOAuthUserResponse($this->createUserResponseMock(null, 'not_configured'));
    }

    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenUserIsNull()
    {
        $userResponseMock = $this->createUserResponseMock('asm89', 'github');

        $provider = $this->createEntityUserProvider();

        try {
            $provider->loadUserByOAuthUserResponse($userResponseMock);

            $this->fail('Failed asserting exception');
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf(UsernameNotFoundException::class, $e);
            $this->assertSame("User 'asm89' not found.", $e->getMessage());
            $this->assertSame('asm89', $e->getUsername());
        }
    }

    public function testLoadUserByOAuthUserResponse()
    {
        $userResponseMock = $this->createUserResponseMock('asm89', 'github');

        $user = new User();
        $provider = $this->createEntityUserProvider($user);

        $loadedUser = $provider->loadUserByOAuthUserResponse($userResponseMock);

        $this->assertEquals($user, $loadedUser);
    }

    public function testRefreshUserThrowsExceptionWhenUserIsNull()
    {
        $provider = $this->createEntityUserProvider();
        $user = new User();

        try {
            $provider->refreshUser($user);

            $this->fail('Failed asserting exception');
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf(UsernameNotFoundException::class, $e);
            $this->assertSame('User with ID "1" could not be reloaded.', $e->getMessage());
            $this->assertSame('foo', $e->getUsername());
        }
    }

    public function createManagerRegistryMock($user = null)
    {
        $registryMock = $this->createMock(ManagerRegistry::class);

        $registryMock
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($this->createEntityManagerMock($user));

        return $registryMock;
    }

    public function createRepositoryMock($user = null)
    {
        $mock = $this->createMock(ObjectRepository::class);

        if (null !== $user) {
            $mock->expects($this->once())
                ->method('findOneBy')
                ->with(['githubId' => 'asm89'])
                ->willReturn($user);
        }

        return $mock;
    }

    protected function createEntityUserProvider($user = null)
    {
        return new EntityUserProvider(
            $this->createManagerRegistryMock($user),
            User::class,
            [
                'github' => 'githubId',
            ]
        );
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

    protected function createEntityManagerMock($user = null)
    {
        $emMock = $this->createMock(ObjectManager::class);

        $emMock
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->createRepositoryMock($user));

        return $emMock;
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
