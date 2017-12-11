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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;
use PHPUnit\Framework\TestCase;

class EntityUserProviderTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists('Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped('The Doctrine ORM is not available');
        }
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No property defined for entity for resource owner 'not_configured'.
     */
    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenNoPropertyIsConfigured()
    {
        $provider = $this->createEntityUserProvider();
        $provider->loadUserByOAuthUserResponse($this->createUserResponseMock(null, 'not_configured'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage User 'asm89' not found.
     */
    public function testLoadUserByOAuthUserResponseThrowsExceptionWhenUserIsNull()
    {
        $userResponseMock = $this->createUserResponseMock('asm89', 'github');

        $provider = $this->createEntityUserProvider();

        $provider->loadUserByOAuthUserResponse($userResponseMock);
    }

    public function testLoadUserByOAuthUserResponse()
    {
        $userResponseMock = $this->createUserResponseMock('asm89', 'github');

        $user = new User();
        $provider = $this->createEntityUserProvider($user);

        $loadedUser = $provider->loadUserByOAuthUserResponse($userResponseMock);

        $this->assertEquals($user, $loadedUser);
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

    public function createManagerRegistryMock($user = null)
    {
        $registryMock = $this->getMockBuilder(ManagerRegistry::class)
            ->getMock();

        $registryMock
            ->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->createEntityManagerMock($user)));

        return $registryMock;
    }

    public function createRepositoryMock($user = null)
    {
        $mock = $this->getMockBuilder(ObjectRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (null !== $user) {
            $mock->expects($this->once())
                ->method('findOneBy')
                ->with(array('githubId' => 'asm89'))
                ->will($this->returnValue($user));
        }

        return $mock;
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

    protected function createEntityManagerMock($user = null)
    {
        $emMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $emMock
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->createRepositoryMock($user)));

        return $emMock;
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
