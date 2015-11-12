<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Response;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AbstractUserResponse
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
abstract class AbstractUserResponse implements UserResponseInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @var ResourceOwnerInterface
     */
    protected $resourceOwner;

    /**
     * @var OAuthToken
     */
    protected $oAuthToken;

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getProfilePicture()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return $this->oAuthToken->getAccessToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken()
    {
        return $this->oAuthToken->getRefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenSecret()
    {
        return $this->oAuthToken->getTokenSecret();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        return $this->oAuthToken->getExpiresIn();
    }

    /**
     * {@inheritdoc}
     */
    public function setOAuthToken(OAuthToken $token)
    {
        $this->oAuthToken = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getOAuthToken()
    {
        return $this->oAuthToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function setResponse($response)
    {
        if (is_array($response)) {
            $this->response = $response;
        } else {
            // First check that response exists, due too bug: https://bugs.php.net/bug.php?id=54484
            if (!$response) {
                $this->response = array();
            } else {
                $this->response = json_decode($response, true);

                if (JSON_ERROR_NONE !== json_last_error()) {
                    throw new AuthenticationException('Response is not a valid JSON code.');
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwner()
    {
        return $this->resourceOwner;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceOwner(ResourceOwnerInterface $resourceOwner)
    {
        $this->resourceOwner = $resourceOwner;
    }
}
