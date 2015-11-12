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

class OAuthUserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OAuthUser
     */
    private $user;

    public function setUp()
    {
        $this->user = new OAuthUser('asm89');
    }

    public function testGetRoles()
    {
        $this->assertEquals(array('ROLE_USER', 'ROLE_OAUTH_USER'), $this->user->getRoles());
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

        $user = new OAuthUser('other');
        $this->assertEquals('other', $user->getUsername());
    }

    public function testEraseCredentials()
    {
        $this->assertTrue($this->user->eraseCredentials());
    }

    public function testEquals()
    {
        $otherUser = new OAuthUser('other');
        $sameUser  = new OAuthUser('asm89');

        $this->assertFalse($this->user->equals($otherUser));
        $this->assertTrue($this->user->equals($sameUser));
    }
}
