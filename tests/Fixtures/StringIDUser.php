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

final class StringIDUser implements UserInterface
{
    public function getId(): string
    {
        return '6f5f78b2-005d-4eea-96c4-79044aaebb34';
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getUserIdentifier(): string
    {
        return 'abc';
    }

    #[Deprecated]
    public function eraseCredentials(): void
    {
    }
}
