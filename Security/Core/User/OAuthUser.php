<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\User;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 *
 * @final since 1.4
 */
class OAuthUser implements UserInterface
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUserIdentifier()
    {
        return $this->username;
    }

    /**
     * @return array<int, string>
     */
    public function getRoles()
    {
        return ['ROLE_USER', 'ROLE_OAUTH_USER'];
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getUserIdentifier();
    }

    /**
     * @return bool
     */
    public function eraseCredentials()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function equals(UserInterface $user)
    {
        return $user->getUsername() === $this->username;
    }
}
