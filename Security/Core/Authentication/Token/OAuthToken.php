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
     * @var array
     */
    private $rawToken;

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
     * @param string|array $accessToken The OAuth access token
     * @param array        $roles       Roles for the token
     */
    public function __construct($accessToken, array $roles = array())
    {
        parent::__construct($roles);

        $this->setRawToken($accessToken);

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
     * @param array|string $token The OAuth token
     */
    public function setRawToken($token)
    {
        if (is_array($token)) {
            $this->accessToken = $token['access_token'];

            if (isset($token['refresh_token'])) {
                $this->refreshToken = $token['refresh_token'];
            }

            if (isset($token['expires_in'])) {
                $this->expiresIn = $token['expires_in'];
            }

            $this->rawToken = $token;
        } else {
            $this->accessToken = $token;
            $this->rawToken    = array('access_token' => $token);
        }
    }

    /**
     * @return array
     */
    public function getRawToken()
    {
        return $this->rawToken;
    }

    /**
     * @param string $refreshToken The OAuth refresh token
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param integer $expiresIn The duration in seconds of the access token lifetime
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
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
            $this->rawToken,
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
            $this->rawToken,
            $this->refreshToken,
            $this->expiresIn,
            $this->resourceOwnerName,
            $parent,
        ) = unserialize($serialized);

        parent::unserialize($parent);
    }
}
