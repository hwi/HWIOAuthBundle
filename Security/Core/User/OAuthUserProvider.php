<?php

namespace Knp\OAuthBundle\Security\Core\User;

use Symfony\Component\Security\Core\User\UserProviderInterface,
    Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use Knp\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface;

class OAuthUserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        return new OAuthUser($username);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class == 'Knp\\OAuthBundle\\Security\\Core\\User\\OAuthUser';
    }
}