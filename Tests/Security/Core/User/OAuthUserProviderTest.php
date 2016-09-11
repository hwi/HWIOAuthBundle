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

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use Symfony\Component\Security\Core\User\User;

class OAuthUserProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OAuthUserProvider
     */
    protected $provider;

    public function setUp()
    {
        $this->provider = new OAuthUserProvider();
    }

    public function testLoadUserByUsername()
    {
        $user = $this->provider->loadUserByUsername('asm89');
        $this->assertInstanceOf('\HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser', $user);
        $this->assertEquals('asm89', $user->getUsername());
    }

    public function testRefreshUser()
    {
        $user = new OAuthUser('asm89');

        $freshUser = $this->provider->refreshUser($user);
        $this->assertEquals($user, $freshUser);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     * @expectedExceptionMessage Unsupported user class "Symfony\Component\Security\Core\User\User"
     */
    public function testRefreshUserUnsupportedClass()
    {
        $user = new User('asm89', 'foo');

        $this->provider->refreshUser($user);
    }

    public function testSupportsClass()
    {
        $class = get_class(new OAuthUser('asm89'));

        $this->assertTrue($this->provider->supportsClass($class));
        $this->assertFalse($this->provider->supportsClass('\Some\Other\Class'));
    }

    public function testLoadUserByOAuthUserResponse()
    {
        $responseMock = $this->getMockBuilder('HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock
            ->expects($this->once())
            ->method('getNickname')
            ->will($this->returnValue('asm89'))
        ;

        $user = $this->provider->loadUserByOAuthUserResponse($responseMock);
        $this->assertInstanceOf('\HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser', $user);
        $this->assertEquals('asm89', $user->getUsername());
    }
}
