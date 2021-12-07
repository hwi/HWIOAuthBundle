<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\User;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
final class OAuthUser implements UserInterface
{
    private string $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return array<int, string>
     */
    public function getRoles(): array
    {
        return ['ROLE_USER', 'ROLE_OAUTH_USER'];
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @return bool
     */
    public function eraseCredentials(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function equals(UserInterface $user): bool
    {
        return $user->getUsername() === $this->username;
    }
}
