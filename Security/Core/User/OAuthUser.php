<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\User;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * OAuthUser.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
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
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return array('ROLE_USER', 'ROLE_OAUTH_USER');
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(UserInterface $user)
    {
        return $user->getUsername() === $this->username;
    }
}
