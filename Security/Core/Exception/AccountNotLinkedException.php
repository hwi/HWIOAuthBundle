<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
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
        return serialize(array(
            $this->resourceOwnerName,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->resourceOwnerName,
            $parentData
        ) = unserialize($str);

        parent::unserialize($parentData);
    }
}
