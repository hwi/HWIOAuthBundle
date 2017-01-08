<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Fixtures;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * OAuthAwareException.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class OAuthAwareException extends \Exception implements OAuthAwareExceptionInterface
{
    /**
     * @var OAuthToken
     */
    protected $token;
    /**
     * @var string
     */
    protected $resourceOwnerName;

    /**
     * {@inheritdoc}
     */
    public function getAccessToken()
    {
        return $this->token->getAccessToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken()
    {
        return $this->token->getRefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn()
    {
        return $this->token->getExpiresIn();
    }

    /**
     * @return OAuthToken
     */
    public function getRawToken()
    {
        return $this->token->getRawToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenSecret()
    {
        return $this->token->getTokenSecret();
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
    public function setToken(TokenInterface $token)
    {
        $this->token = $token;
    }
}
