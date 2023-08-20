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

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use PHPUnit\Framework\TestCase;

final class OAuthUserTest extends TestCase
{
    private OAuthUser $user;

    protected function setUp(): void
    {
        $this->user = new OAuthUser('asm89');
    }

    public function testGetRoles(): void
    {
        $this->assertEquals(['ROLE_USER', 'ROLE_OAUTH_USER'], $this->user->getRoles());
    }

    public function testGetPassword(): void
    {
        $this->assertNull($this->user->getPassword());
    }

    public function testGetSalt(): void
    {
        $this->assertNull($this->user->getSalt());
    }

    public function testGetUsername(): void
    {
        $this->assertEquals('asm89', $this->user->getUserIdentifier());

        $user = new OAuthUser('other');
        $this->assertEquals('other', $user->getUserIdentifier());
    }

    public function testEraseCredentials(): void
    {
        $this->assertTrue($this->user->eraseCredentials());
    }

    public function testEquals(): void
    {
        $otherUser = new OAuthUser('other');
        $sameUser = new OAuthUser('asm89');

        $this->assertFalse($this->user->equals($otherUser));
        $this->assertTrue($this->user->equals($sameUser));
    }
}
