<?php

namespace Knp\OAuthBundle\Security\Core\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Knp\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class OAuthProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken;
    }

    public function authenticate(TokenInterface $token)
    {
        
    }
}