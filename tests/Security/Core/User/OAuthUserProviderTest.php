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

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\InMemoryUser;

final class OAuthUserProviderTest extends TestCase
{
    private OAuthUserProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new OAuthUserProvider();
    }

    public function testLoadUserByUsernameOrIdentifier(): void
    {
        $user = $this->provider->loadUserByIdentifier('asm89');

        $this->assertInstanceOf(OAuthUser::class, $user);
        $this->assertEquals('asm89', $user->getUserIdentifier());
    }

    public function testRefreshUser(): void
    {
        $user = new OAuthUser('asm89');

        $freshUser = $this->provider->refreshUser($user);
        $this->assertEquals($user, $freshUser);
    }

    public function testRefreshUserUnsupportedClass(): void
    {
        $user = new InMemoryUser('asm89', 'foo');
        $message = 'Unsupported user class "Symfony\\Component\\Security\\Core\\User\\InMemoryUser"';

        $this->expectException(UnsupportedUserException::class);
        $this->expectExceptionMessage($message);

        $this->provider->refreshUser($user);
    }

    public function testSupportsClass(): void
    {
        $class = \get_class(new OAuthUser('asm89'));

        $this->assertTrue($this->provider->supportsClass($class));
        $this->assertFalse($this->provider->supportsClass('\Some\Other\Class'));
    }

    public function testLoadUserByOAuthUserResponse(): void
    {
        $responseMock = $this->createMock(UserResponseInterface::class);

        $responseMock
            ->expects($this->once())
            ->method('getNickname')
            ->willReturn('asm89')
        ;

        $user = $this->provider->loadUserByOAuthUserResponse($responseMock);
        $this->assertInstanceOf(OAuthUser::class, $user);

        $this->assertEquals('asm89', $user->getUserIdentifier());
    }
}
