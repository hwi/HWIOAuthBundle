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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;

/**
 * OAuthProvider
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class OAuthProvider implements AuthenticationProviderInterface
{
    /**
     * @var ResourceOwnerMap
     */
    private $resourceOwnerMap;

    /**
     * @var OAuthAwareUserProviderInterface
     */
    private $userProvider;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @param OAuthAwareUserProviderInterface $userProvider     User provider
     * @param ResourceOwnerMap                $resourceOwnerMap Resource owner map
     * @param UserCheckerInterface            $userChecker      User checker
     */
    public function __construct(OAuthAwareUserProviderInterface $userProvider, ResourceOwnerMap $resourceOwnerMap, UserCheckerInterface $userChecker)
    {
        $this->userProvider     = $userProvider;
        $this->resourceOwnerMap = $resourceOwnerMap;
        $this->userChecker      = $userChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(TokenInterface $token)
    {
        return
            $token instanceof OAuthToken
            && $this->resourceOwnerMap->hasResourceOwnerByName($token->getResourceOwnerName())
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return;
        }

        /* @var OAuthToken $token */
        $resourceOwner = $this->resourceOwnerMap->getResourceOwnerByName($token->getResourceOwnerName());

        $userResponse = $resourceOwner->getUserInformation($token->getRawToken());

        try {
            $user = $this->userProvider->loadUserByOAuthUserResponse($userResponse);
        } catch (OAuthAwareExceptionInterface $e) {
            $e->setToken($token);
            $e->setResourceOwnerName($token->getResourceOwnerName());

            throw $e;
        }

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('loadUserByOAuthUserResponse() must return a UserInterface.');
        }

        try {
            $this->userChecker->checkPreAuth($user);
            $this->userChecker->checkPostAuth($user);
        } catch (BadCredentialsException $e) {
            if ($this->hideUserNotFoundExceptions) {
                throw new BadCredentialsException('Bad credentials', 0, $e);
            }

            throw $e;
        }

        $token = new OAuthToken($token->getRawToken(), $user->getRoles());
        $token->setResourceOwnerName($resourceOwner->getName());
        $token->setUser($user);
        $token->setAuthenticated(true);

        return $token;
    }
}
