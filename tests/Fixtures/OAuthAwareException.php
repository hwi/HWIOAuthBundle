<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Fixtures;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\AbstractOAuthToken;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\OAuthAwareExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
final class OAuthAwareException extends \Exception implements OAuthAwareExceptionInterface
{
    private AbstractOAuthToken $token;
    private string $resourceOwnerName;

    /**
     * {@inheritdoc}
     */
    public function getAccessToken(): string
    {
        return $this->token->getAccessToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken(): ?string
    {
        return $this->token->getRefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresIn(): ?int
    {
        return $this->token->getExpiresIn();
    }

    /**
     * {@inheritdoc}
     */
    public function getRawToken(): array
    {
        return $this->token->getRawToken();
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenSecret(): ?string
    {
        return $this->token->getTokenSecret();
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceOwnerName(): string
    {
        return $this->resourceOwnerName;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceOwnerName($resourceOwnerName): void
    {
        $this->resourceOwnerName = $resourceOwnerName;
    }

    /**
     * @param AbstractOAuthToken $token
     *
     * {@inheritdoc}
     */
    public function setToken(TokenInterface $token): void
    {
        $this->token = $token;
    }
}
