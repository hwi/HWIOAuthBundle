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
 * OAuthToken.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
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
     * @var int
     */
    private $expiresIn;

    /**
     * @var int
     */
    private $createdAt;

    /**
     * @var string
     */
    private $tokenSecret;

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
     * {@inheritdoc}
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
     *
     * @throws \InvalidArgumentException
     */
    public function setRawToken($token)
    {
        if (is_array($token)) {
            if (isset($token['access_token'])) {
                $this->accessToken = $token['access_token'];
            } elseif (isset($token['oauth_token'])) {
                $this->accessToken = $token['oauth_token'];
            } else {
                throw new \InvalidArgumentException('Access token was not found.');
            }

            if (isset($token['refresh_token'])) {
                $this->refreshToken = $token['refresh_token'];
            }

            if (isset($token['expires_in'])) {
                $this->setExpiresIn($token['expires_in']);
            } elseif (isset($token['oauth_expires_in'])) {
                $this->setExpiresIn($token['oauth_expires_in']);
            } elseif (isset($token['expires'])) {
                // Facebook unfortunately breaks the spec by using 'expires' instead of 'expires_in'
                $this->setExpiresIn($token['expires']);
            }

            if (isset($token['oauth_token_secret'])) {
                $this->tokenSecret = $token['oauth_token_secret'];
            }

            $this->rawToken = $token;
        } else {
            $this->accessToken = $token;
            $this->rawToken = array('access_token' => $token);
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
     * @param int $expiresIn The duration in seconds of the access token lifetime
     */
    public function setExpiresIn($expiresIn)
    {
        $this->createdAt = time();
        $this->expiresIn = $expiresIn;
    }

    /**
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @param int $createdAt The token creation date in seconds
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getExpiresAt()
    {
        if (null === $this->expiresIn) {
            return null;
        }

        return $this->createdAt + $this->expiresIn;
    }

    /**
     * @param string $tokenSecret
     */
    public function setTokenSecret($tokenSecret)
    {
        $this->tokenSecret = $tokenSecret;
    }

    /**
     * @return null|string
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }

    /**
     * Returns if the `access_token` is expired.
     *
     * @return bool true if the `access_token` is expired
     */
    public function isExpired()
    {
        if (null === $this->expiresIn) {
            return false;
        }

        return ($this->createdAt + $this->expiresIn - time()) < 30;
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
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->accessToken,
            $this->rawToken,
            $this->refreshToken,
            $this->expiresIn,
            $this->createdAt,
            $this->resourceOwnerName,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when un-serializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 4, null));

        list(
            $this->accessToken,
            $this->rawToken,
            $this->refreshToken,
            $this->expiresIn,
            $this->createdAt,
            $this->resourceOwnerName,
            $parent) = $data;

        if (!$this->tokenSecret && isset($this->rawToken['oauth_token_secret'])) {
            $this->tokenSecret = $this->rawToken['oauth_token_secret'];
        }

        parent::unserialize($parent);
    }
}
