<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\RequestDataStorage;

use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorageInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Request token storage implementation using the Symfony session.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Francisco Facioni <fran6co@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
final class SessionStorage implements RequestDataStorageInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(ResourceOwnerInterface $resourceOwner, $key, $type = 'token')
    {
        $key = $this->generateKey($resourceOwner, $key, $type);
        if (null === $data = $this->getSession()->get($key)) {
            throw new \InvalidArgumentException('No data available in storage.');
        }

        // Request tokens are one time use only
        if (\in_array($type, ['token', 'csrf_state'], true)) {
            $this->getSession()->remove($key);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ResourceOwnerInterface $resourceOwner, $value, $type = 'token')
    {
        if ('token' === $type) {
            if (!\is_array($value) || !isset($value['oauth_token'])) {
                throw new \InvalidArgumentException('Invalid request token.');
            }

            $key = $this->generateKey($resourceOwner, $value['oauth_token'], 'token');
        } else {
            $key = $this->generateKey($resourceOwner, $this->getStorageKey($value), $type);
        }

        $this->getSession()->set($key, $this->getStorageValue($value));
    }

    /**
     * Key to for fetching or saving a token.
     */
    private function generateKey(ResourceOwnerInterface $resourceOwner, string $key, string $type): string
    {
        return sprintf('_hwi_oauth.%s.%s.%s.%s', $resourceOwner->getName(), $resourceOwner->getOption('client_id'), $type, $key);
    }

    /**
     * @param array|string|object $value
     *
     * @return array|string
     */
    private function getStorageValue($value)
    {
        if (\is_object($value)) {
            $value = serialize($value);
        }

        return $value;
    }

    /**
     * @param array|string|object $value
     */
    private function getStorageKey($value): string
    {
        if (\is_array($value)) {
            $storageKey = reset($value);
        } elseif (\is_object($value)) {
            $storageKey = \get_class($value);
        } else {
            $storageKey = $value;
        }

        return (string) $storageKey;
    }

    private function getSession(): SessionInterface
    {
        if (method_exists($this->requestStack, 'getSession')) {
            return $this->requestStack->getSession();
        }

        if ((null !== $request = $this->requestStack->getCurrentRequest()) && $request->hasSession()) {
            return $request->getSession();
        }

        throw new \LogicException('There is currently no session available.');
    }
}
