<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http\Firewall;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractListener;

abstract class AbstractRefreshAccessTokenListener extends AbstractListener
{
    protected TokenStorageInterface $tokenStorage;

    protected ResourceOwnerMap $resourceOwnerMap;

    protected bool $enabled = false;

    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function setResourceOwnerMap(ResourceOwnerMap $resourceOwnerMap)
    {
        $this->resourceOwnerMap = $resourceOwnerMap;
    }

    public function enable(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function supports(Request $request): ?bool
    {
        return $this->enabled;
    }

    public function authenticate(RequestEvent $event): void
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!$token instanceof OAuthToken) {
            return;
        }

        if (false === $token->isExpired()) {
            return;
        }

        $resourceOwner = $this->resourceOwnerMap->getResourceOwnerByName($token->getResourceOwnerName());

        if (!$resourceOwner instanceof GenericOAuth2ResourceOwner) {
            return;
        }

        if (!$resourceOwner->shouldRefreshOnExpire()) {
            return;
        }

        // here not clear what were better,
        // * silent stop or
        // * a logger with a notice or
        // * force user to logout
        if (!$token->getRefreshToken()) {
            return;
        }

        try {
            $newToken = $this->refreshToken($token);
        } catch (AuthenticationException $e) {
            $newToken = null;
        }

        $this->tokenStorage->setToken($newToken);
    }

    /**
     * @template T of OAuthToken
     *
     * @param T $token
     *
     * @return T
     */
    abstract protected function refreshToken(OAuthToken $token): OAuthToken;
}
