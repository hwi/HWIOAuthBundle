<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Exception;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\AbstractOAuthToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * @final since 1.4
 */
class AccountNotLinkedException extends UsernameNotFoundException implements OAuthAwareExceptionInterface
{
    /**
     * @var string
     */
    protected $resourceOwnerName;

    /**
     * {@inheritdoc}
     */
    public function __serialize(): array
    {
        return [
            $this->resourceOwnerName,
            parent::__serialize(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function __unserialize(array $data): void
    {
        [
            $this->resourceOwnerName,
            $parentData
        ] = $data;

        parent::__unserialize($parentData);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Account could not be linked correctly.';
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        /** @var AbstractOAuthToken $token */
        $token = $this->getToken();

        return $token->getAccessToken();
    }

    /**
     * @return array
     */
    public function getRawToken()
    {
        /** @var AbstractOAuthToken $token */
        $token = $this->getToken();

        return $token->getRawToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken()
    {
        /** @var AbstractOAuthToken $token */
        $token = $this->getToken();

        return $token->getRefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        /** @var AbstractOAuthToken $token */
        $token = $this->getToken();

        return $token->getExpiresIn();
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenSecret()
    {
        /** @var AbstractOAuthToken $token */
        $token = $this->getToken();

        return $token->getTokenSecret();
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerName()
    {
        return $this->resourceOwnerName;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceOwnerName($resourceOwnerName)
    {
        $this->resourceOwnerName = $resourceOwnerName;
    }
}
