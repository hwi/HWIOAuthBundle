<?php

namespace HWI\Bundle\OAuthBundle\Security\Core\Authentication\Provider;

use Buzz\Exception\RequestException;
use HWI\Bundle\OAuthBundle\OAuth\Exception\HttpTransportException;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\RageOAuthAwareUserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;

class RageOAuthProvider implements AuthenticationProviderInterface
{
    /**
     * @var ResourceOwnerMap
     */
    private $resourceOwnerMap;

    /**
     * @var RageOAuthAwareUserProviderInterface
     */
    private $userProvider;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @param RageOAuthAwareUserProviderInterface $userProvider     User provider
     * @param ResourceOwnerMap                $resourceOwnerMap Resource owner map
     * @param UserCheckerInterface            $userChecker      User checker
     */
    public function __construct(RageOAuthAwareUserProviderInterface $userProvider, ResourceOwnerMap $resourceOwnerMap, UserCheckerInterface $userChecker)
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
        return $token instanceof OAuthToken;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        /* @var OAuthToken $token */
        $resourceOwner = $this->resourceOwnerMap->getResourceOwnerByName($token->getResourceOwnerName());

        if ($token->getUser()) {
            $user = $this->userProvider->refreshUser($token->getUser());
        } else {
            try {
                $userResponse = $resourceOwner->getUserInformation($token->getRawToken());
            } catch (HttpTransportException $e) {
                $token = new AnonymousToken($token->getRawToken(), 'anon.');
                $token->setAuthenticated(true);
                return $token;
            } catch (RequestException $e) {
                $token = new AnonymousToken($token->getRawToken(), 'anon.');
                $token->setAuthenticated(true);
                return $token;
            }
            try {
                $user = $this->userProvider->loadUserByOAuthUserResponse($userResponse);
            } catch (OAuthAwareExceptionInterface $e) {
                $e->setToken($token);
                $e->setResourceOwnerName($token->getResourceOwnerName());
                throw $e;
            }
        }

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('loadUserByOAuthUserResponse() must return a UserInterface.');
        }

        try {
            $this->userChecker->checkPreAuth($user);
            $this->userChecker->checkPostAuth($user);
        } catch (BadCredentialsException $e) {
            throw $e;
        }

        $token = new OAuthToken($token->getRawToken(), $user->getRoles());
        $token->setResourceOwnerName($resourceOwner->getName());
        $token->setUser($user);
        $token->setAuthenticated(true);
        return $token;
    }
}
