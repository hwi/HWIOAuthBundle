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

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface,
    HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser,
    HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;

use Symfony\Component\Security\Core\User\UserProviderInterface,
    Symfony\Component\Security\Core\User\UserInterface,
    Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * OAuthUserProvider
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class OAuthUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        return new OAuthUser($username);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        return $this->loadUserByUsername($response->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === 'HWI\\Bundle\\OAuthBundle\\Security\\Core\\User\\OAuthUser';
    }
}
