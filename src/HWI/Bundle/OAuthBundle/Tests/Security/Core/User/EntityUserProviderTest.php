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

use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use HWI\Bundle\OAuthBundle\Tests\Fixtures\User;

class EntityUserProviderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
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
        $class = 'HWI\Bundle\OAuthBundle\Tests\Fixtures\User';
        $properties = array('github' => 'githubId');

        return new EntityUserProvider($this->createManagerRegistryMock($user), $class, $properties);
    }

    public function createManagerRegistryMock($user = null)
    {
        $registryMock = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->getMock();

        $registryMock
            ->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->createEntityManagerMock($user)));

        return $registryMock;
    }

    public function createRepositoryMock($user = null)
    {
        $mock = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
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
        $resourceOwnerMock = $this->getMock('HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface');

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
        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
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
