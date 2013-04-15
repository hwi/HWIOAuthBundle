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
     * @var mixed
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $oauthToken;

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
        if (is_array($accessToken) && isset($accessToken['oauth_token'])) {
            $this->oauthToken = $accessToken['oauth_token'];
        }

        $this->accessToken = $accessToken;
    }

    /**
     * @return array|null
     */
    public function getOAuthToken()
    {
        return $this->oauthToken ?: $this->accessToken;
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
        $this->response = json_decode($response, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new AuthenticationException('Not a valid JSON response.');
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
