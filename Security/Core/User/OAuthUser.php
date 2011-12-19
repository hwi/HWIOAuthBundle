<?php

namespace Knp\OAuthBundle\Security\Core\User;

use Symfony\Component\Security\Core\User\UserInterface;

class OAuthUser implements UserInterface
{
    private $username = 'NONE_PROVIDED';

    private $accessToken;

    public function __construct($username)
    {
        $this->username = $username;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
        return true;
    }

    public function equals(UserInterface $user)
    {
        return $user->getUsername() == $this->getUsername();
    }
}