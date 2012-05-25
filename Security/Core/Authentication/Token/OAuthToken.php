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
     * @var string
     */
    private $accessToken;

    /**
     * @var string
     */
    private $resourceOwnerName;

    /**
     * @param string $accessToken The OAuth access token
     * @param array  $roles       Roles for the token
     */
    public function __construct($accessToken, array $roles = array())
    {
        $this->accessToken = $accessToken;

        parent::__construct($roles);
    }

    /**
     * {@inheritDoc}
     */
    public function getCredentials()
    {
        return $this->accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthenticated()
    {
        return count($this->getRoles()) > 0;
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

    public function serialize()
    {
        $this->setAttribute('accessToken', $this->accessToken);
        return parent::serialize();
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        parent::unserialize($serialized);
        $this->accessToken = $this->getAttribute('accessToken');
    }
}
