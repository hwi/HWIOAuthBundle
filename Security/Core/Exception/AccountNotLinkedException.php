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

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

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
            $this->serializationFromParent(),
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

        $this->unserializationFromParent($parentData);
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
        return $this->getToken()->getAccessToken();
    }

    /**
     * @return array
     */
    public function getRawToken()
    {
        return $this->getToken()->getRawToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken()
    {
        return $this->getToken()->getRefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        return $this->getToken()->getExpiresIn();
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenSecret()
    {
        return $this->getToken()->getTokenSecret();
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

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->__serialize());
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        $this->__unserialize((array) unserialize((string) $str));
    }

    /**
     * Symfony < 4.3 BC layer.
     */
    private function serializationFromParent(): array
    {
        if (method_exists(UsernameNotFoundException::class, '__serialize')) {
            return parent::__serialize();
        }

        return unserialize(parent::serialize());
    }

    /**
     * Symfony < 4.3 BC layer.
     */
    private function unserializationFromParent(array $parentData): void
    {
        if (method_exists(UsernameNotFoundException::class, '__unserialize')) {
            parent::__unserialize($parentData);
        } else {
            parent::unserialize(serialize($parentData));
        }
    }
}
