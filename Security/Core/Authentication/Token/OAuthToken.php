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
    private $refreshToken;

    /**
     * @var integer
     */
    private $expiresIn;

    /**
     * @var string
     */
    private $resourceOwnerName;

    /**
     * @param mixed   $accessToken  The OAuth access token
     * @param array   $roles        Roles for the token
     * @param string  $refreshToken The OAuth refresh token
     * @param integer $expiresIn    The duration in seconds of the access token lifetime
     */
    public function __construct($accessToken, array $roles = array(),
        $refreshToken = null, $expiresIn = null)
    {
        parent::__construct($roles);

        if (is_array($accessToken)) {
            $this->accessToken = $accessToken['access_token'];
            if (isset($accessToken['refresh_token'])) {
                $this->refreshToken = $accessToken['refresh_token'];
            }

            if (isset($accessToken['expires_in'])) {
                $this->expiresIn = $accessToken['expires_in'];
            }

        } else {
            $this->accessToken = $accessToken;
            $this->refreshToken = $refreshToken;
            $this->expiresIn = $expiresIn;
        }

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
     * @param string $accessToken The OAuth access token
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return integer
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
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
            $this->refreshToken,
            $this->expiresIn,
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
            $this->refreshToken,
            $this->expiresIn,
            $this->resourceOwnerName,
            $parent,
        ) = unserialize($serialized);

        parent::unserialize($parent);
    }
}
