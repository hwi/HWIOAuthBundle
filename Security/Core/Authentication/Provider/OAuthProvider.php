<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface,
    Symfony\Component\Security\Core\Authentication\Token\TokenInterface,
    Symfony\Component\Security\Core\User\UserProviderInterface;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface,
    HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken,
    HWI\Bundle\OAuthBundle\Security\Exception\AccessTokenAwareExceptionInterface;

/**
 * OAuthProvider
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class OAuthProvider implements AuthenticationProviderInterface
{
    /**
     * @var HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface
     */
    private $resourceOwner;

    /**
     * @var Symfony\Component\Security\Core\User\UserProviderInterface
     */
    private $userProvider;

    /**
     * @param Symfony\Component\Security\Core\User\UserProviderInterface $userProvider
     * @param HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface $resourceOwner
     */
    public function __construct(UserProviderInterface $userProvider, ResourceOwnerInterface $resourceOwner)
    {
        $this->userProvider  = $userProvider;
        $this->resourceOwner = $resourceOwner;
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
        $username = $this->resourceOwner
            ->getUserInformation($token->getCredentials())
            ->getUsername();

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
