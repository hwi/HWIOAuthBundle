<?php

namespace Knp\OAuthBundle\Security\Core\User;

use Symfony\Component\Security\Core\User\UserProviderInterface,
    Symfony\Component\Security\Core\User\UserInterface;

class OAuthUserProvider implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        return new OAuthUser($username);
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class == 'Knp\\OAuthBundle\\Security\\Core\\OAuthUser';
    }
}