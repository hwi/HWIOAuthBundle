<?php

namespace Knp\OAuthBundle\Security\Core\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

use Knp\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Knp\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface;

class OAuthProvider implements AuthenticationProviderInterface
{
    private $oauthProvider;

    private $userProvider;

    public function __construct(UserProviderInterface $userProvider, OAuthProviderInterface $oauthProvider)
    {
        $this->userProvider  = $userProvider;
        $this->oauthProvider = $oauthProvider;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken;
    }

    public function authenticate(TokenInterface $token)
    {
        $username = $this->oauthProvider->getUsername($token->getCredentials());
        $user     = $this->userProvider->loadUserByUsername($username);

        $token = new OAuthToken($token->getCredentials(), $user->getRoles());
        $token->setUser($user);
        $token->setAuthenticated(true);

        return $token;
    }
}