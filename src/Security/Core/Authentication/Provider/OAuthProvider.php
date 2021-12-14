<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Authentication\Provider;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
final class OAuthProvider implements AuthenticationProviderInterface
{
    private OAuthAwareUserProviderInterface $userProvider;
    private ResourceOwnerMapInterface $resourceOwnerMap;
    private UserCheckerInterface $userChecker;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        OAuthAwareUserProviderInterface $userProvider,
        ResourceOwnerMapInterface $resourceOwnerMap,
        UserCheckerInterface $userChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->userProvider = $userProvider;
        $this->resourceOwnerMap = $resourceOwnerMap;
        $this->userChecker = $userChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TokenInterface $token): bool
    {
        if (!$token instanceof OAuthToken) {
            return false;
        }

        return $this->resourceOwnerMap->hasResourceOwnerByName($token->getResourceOwnerName());
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(TokenInterface $token): ?TokenInterface
    {
        if (!$this->supports($token)) {
            return null;
        }

        // If token is authenticated, re-create it to reload user details
        /** @var OAuthToken $token */
        if (!$token->isExpired() && null !== $token->getUser()) {
            /** @var UserInterface $user */
            $user = $token->getUser();

            return $this->createOAuthToken($token->getRawToken(), $token, $user);
        }

        /** @var ResourceOwnerInterface $resourceOwner */
        $resourceOwner = $this->resourceOwnerMap->getResourceOwnerByName($token->getResourceOwnerName());

        $oldToken = $token->isExpired() ? $this->refreshToken($token, $resourceOwner) : $token;
        $userResponse = $resourceOwner->getUserInformation($oldToken->getRawToken());

        try {
            $user = $this->userProvider->loadUserByOAuthUserResponse($userResponse);
        } catch (OAuthAwareExceptionInterface $e) {
            $e->setToken($oldToken);
            $e->setResourceOwnerName($oldToken->getResourceOwnerName());

            throw $e;
        }

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('loadUserByOAuthUserResponse() must return a UserInterface.');
        }

        $this->userChecker->checkPreAuth($user);
        $this->userChecker->checkPostAuth($user);

        return $this->createOAuthToken($oldToken->getRawToken(), $oldToken, $user);
    }

    /**
     * @param OAuthToken $expiredToken
     */
    private function refreshToken(TokenInterface $expiredToken, ResourceOwnerInterface $resourceOwner): OAuthToken
    {
        if (!$expiredToken->getRefreshToken()) {
            return $expiredToken;
        }

        /** @var UserInterface $user */
        $user = $expiredToken->getUser();

        $token = $this->createOAuthToken(
            $resourceOwner->refreshAccessToken($expiredToken->getRefreshToken()),
            $expiredToken,
            $user
        );

        $this->tokenStorage->setToken($token);

        return $token;
    }

    /**
     * @template T of OAuthToken
     *
     * @param string|array $data
     * @param T            $oldToken
     *
     * @returns T
     */
    private function createOAuthToken(
        $data,
        OAuthToken $oldToken,
        ?UserInterface $user
    ): OAuthToken {
        $tokenClass = \get_class($oldToken);
        if (null !== $user) {
            $token = new $tokenClass($data, $user->getRoles());
            $token->setUser($user);
        } else {
            $token = new $tokenClass($data);
        }
        $token->setResourceOwnerName($oldToken->getResourceOwnerName());
        $token->setCreatedAt($oldToken->isExpired() ? time() : $oldToken->getCreatedAt());

        // required for compatibility with Symfony 5.4
        if (method_exists($token, 'setAuthenticated')) {
            $token->setAuthenticated(true, false);
        }

        // Don't use old data if newer was already set
        if (!$token->getRefreshToken()) {
            $token->setRefreshToken($oldToken->getRefreshToken());
        }

        $token->setAttributes($oldToken->getAttributes());

        $token->copyPersistentDataFrom($oldToken);

        return $token;
    }
}
