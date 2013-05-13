<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * OAuthToken
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class OAuthToken extends AbstractToken
{
    /**
     * @var string|array
     */
    private $accessToken;

    /**
     * @var string
     */
    private $resourceOwnerName;

    /**
     * @param string|array $accessToken The OAuth access token
     * @param array        $roles       Roles for the token
     */
    public function __construct($accessToken, array $roles = array())
    {
        parent::__construct($roles);

        $this->accessToken = $accessToken;

        parent::setAuthenticated(count($roles) > 0);
    }

    /**
     * {@inheritDoc}
     */
    public function getCredentials()
    {
        return '';
    }

    /**
     * @param string|array $accessToken The OAuth access token
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string|array
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get the resource owner name.
     *
     * @return string
     */
    public function getResourceOwnerName()
    {
        return $this->resourceOwnerName;
    }

    /**
     * Set the resource owner name.
     *
     * @param string $resourceOwnerName
     */
    public function setResourceOwnerName($resourceOwnerName)
    {
        $this->resourceOwnerName = $resourceOwnerName;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->accessToken,
            $this->resourceOwnerName,
            parent::serialize()
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->accessToken,
            $this->resourceOwnerName,
            $parent,
        ) = unserialize($serialized);

        parent::unserialize($parent);
    }
}
