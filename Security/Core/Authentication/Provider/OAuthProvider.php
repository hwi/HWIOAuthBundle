<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\Security\Core\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface,
    Symfony\Component\Security\Core\Authentication\Token\TokenInterface,
    Symfony\Component\Security\Core\User\UserProviderInterface;

use Knp\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken,
    Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface,
    Knp\Bundle\OAuthBundle\Security\Exception\AccessTokenAwareExceptionInterface;

/**
 * OAuthProvider
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class OAuthProvider implements AuthenticationProviderInterface
{
    /**
     * @var Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface
     */
    private $oauthProvider;

    /**
     * @var Symfony\Component\Security\Core\User\UserProviderInterface
     */
    private $userProvider;

    /**
     * @param Symfony\Component\Security\Core\User\UserProviderInterface $userProvider
     * @param Knp\Bundle\OAuthBundle\Security\Http\OAuth\OAuthProviderInterface $oauthProvider
     */
    public function __construct(UserProviderInterface $userProvider, OAuthProviderInterface $oauthProvider)
    {
        $this->userProvider  = $userProvider;
        $this->oauthProvider = $oauthProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $username = $this->oauthProvider->getUsername($token->getCredentials());

        try {
            $user = $this->userProvider->loadUserByUsername($username);
        } catch (AccessTokenAwareExceptionInterface $e) {
            $e->setAccessToken($token->getCredentials());
            throw $e;
        }

        $token = new OAuthToken($token->getCredentials(), $user->getRoles());
        $token->setUser($user);
        $token->setAuthenticated(true);

        return $token;
    }
}