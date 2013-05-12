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
    private $accessToken;
    private $rawToken;
    private $resourceOwnerName;

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawToken()
    {
        return $this->rawToken;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawToken($token)
    {
        $this->rawToken = is_string($token) ? array('access_token' => $token) : $token;
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

    public function serialize()
    {
        return serialize(array(
            $this->accessToken,
            $this->rawToken,
            $this->resourceOwnerName,
            parent::serialize(),
        ));
    }

    public function unserialize($str)
    {
        list(
            $this->accessToken,
            $this->rawToken,
            $this->resourceOwnerName,
            $parentData
        ) = unserialize($str);
        parent::unserialize($parentData);
    }
}
