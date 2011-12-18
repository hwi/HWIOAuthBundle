<?php

namespace Knp\OAuthBundle\Security\Core\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Knp\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class OAuthProvider implements AuthenticationProviderInterface
{
    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken;
    }

    public function authenticate(TokenInterface $token)
    {
        $token = new OAuthToken($token->getCredentials(), array('ROLE_USER'));
        $token->setAuthenticated(true);

        return $token;
    }
}