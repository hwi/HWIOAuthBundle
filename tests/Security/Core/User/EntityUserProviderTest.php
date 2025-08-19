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

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class EntityUserProviderTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(EntityManager::class)) {
            $this->markTestSkipped('The Doctrine ORM is not available');
        }
        if (!interface_exists(ManagerRegistry::class)) {
            $this->markTestSkipped('The Doctrine ORM is too old');
        }
    }

    public function testLoadUserByIdentifierThrowsExceptionWhenUserIsNotFound(): void
    {
        if (!class_exists(UserNotFoundException::class)) {
            $this->markTestSkipped('This test only runs on Symfony >= 5.3');
        }

        $provider = $this->createEntityUserProvider();

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User \'asm89\' not found.');

        $provider->loadUserByIdentifier('asm89');
    }

    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenNoPropertyIsConfigured(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No property defined for entity for resource owner \'not_configured\'.');

        $provider = $this->createEntityUserProvider();
        $provider->loadUserByOAuthUserResponse($this->createUserResponseMock(null, 'not_configured'));
    }

    #[IgnoreDeprecations]
    #[Group('legacy')]
    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenUserIsNull(): void
    {
        $this->assertUserNotFoundException();
        $this->expectExceptionMessage("User 'asm89' not found.");

        $userResponseMock = $this->createUserResponseMock('asm89', 'github');

        $provider = $this->createEntityUserProvider();
        $provider->loadUserByOAuthUserResponse($userResponseMock);
    }

    #[IgnoreDeprecations]
    #[Group('legacy')]
    public function testLoadUserByOAuthUserResponse(): void
    {
        $userResponseMock = $this->createUserResponseMock('asm89', 'github');

        $user = new User();
        $provider = $this->createEntityUserProvider($user);

        $loadedUser = $provider->loadUserByOAuthUserResponse($userResponseMock);

        $this->assertEquals($user, $loadedUser);
    }

    public function testRefreshUserThrowsExceptionWhenUserIsNull(): void
    {
        $this->assertUserNotFoundException();
        $this->expectExceptionMessage('User with ID "1" could not be reloaded.');

        $provider = $this->createEntityUserProvider();
        $provider->refreshUser(new User());
    }

    private function createManagerRegistryMock($user = null)
    {
        $registryMock = $this->createMock(ManagerRegistry::class);

        $registryMock
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($this->createEntityManagerMock($user));

        return $registryMock;
    }

    private function createRepositoryMock($user = null)
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

    private function createEntityUserProvider($user = null): EntityUserProvider
    {
        return new EntityUserProvider(
            $this->createManagerRegistryMock($user),
            User::class,
            [
                'github' => 'githubId',
            ]
        );
    }

    private function createResourceOwnerMock($resourceOwnerName = null)
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

    private function createEntityManagerMock($user = null)
    {
        $emMock = $this->createMock(ObjectManager::class);

        $emMock
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->createRepositoryMock($user));

        return $emMock;
    }

    private function createUserResponseMock($username = null, $resourceOwnerName = null)
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

    private function assertUserNotFoundException(): void
    {
        $this->expectException(UserNotFoundException::class);
    }
}
