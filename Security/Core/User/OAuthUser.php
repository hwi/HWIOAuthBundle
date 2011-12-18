<?php

namespace Knp\OAuthBundle\Security\Core\User;

use Symfony\Component\Security\Core\User\UserInterface;

class OAuthUser implements UserInterface
{
    private $username;

    public function __construct($username)
    {
        $this->username   = $username;
    }

    function getRoles()
    {
      return array('ROLE_USER');
    }

    function getPassword()
    {
      return null;
    }

    function getSalt()
    {
      return null;
    }

    function getUsername()
    {
      return $this->username;
    }

    function eraseCredentials()
    {
      return true;
    }

    function equals(UserInterface $user)
    {
      return $user->getUsername() == $this->getUsername();
    }
}