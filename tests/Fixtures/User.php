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

use Deprecated;
use Symfony\Component\Security\Core\User\UserInterface;

final class User implements UserInterface
{
    private ?string $plainPassword = null;
    private string $username = 'foo';
    private ?string $email = null;
    private ?string $githubId = null;

    public function getId(): string
    {
        return '1';
    }

    public function getUserIdentifier(): string
    {
        return 'foo';
    }

    public function setUsername($username): void
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return 'secret';
    }

    public function getSalt(): string
    {
        return 'my_salt';
    }

    #[Deprecated]
    public function eraseCredentials(): void
    {
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function getGithubId(): ?string
    {
        return $this->githubId;
    }

    public function setGithubId($githubId): void
    {
        $this->githubId = $githubId;
    }
}
