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

class OAuthUserTest extends \PHPUnit_Framework_Testcase
{
    private $user;

    public function setup()
    {
        $this->user = new OAuthUser('asm89');
    }

    public function testGetRoles()
    {
        $this->assertEquals(array('ROLE_USER'), $this->user->getRoles());
    }

    public function testGetPassword()
    {
        $this->assertNull($this->user->getPassword());
    }

    public function testGetSalt()
    {
        $this->assertNull($this->user->getSalt());
    }

    public function testGetUsername()
    {
        $this->assertEquals('asm89', $this->user->getUsername());
    }

    public function testEraseCredentials()
    {
        $this->assertTrue($this->user->eraseCredentials());
    }

    public function testEquals()
    {
        $user = new OAuthUser('other');
        $user2 = new OAuthUser('asm89');

        $this->assertFalse($this->user->equals($user));
        $this->assertTrue($this->user->equals($user2));
    }
}
