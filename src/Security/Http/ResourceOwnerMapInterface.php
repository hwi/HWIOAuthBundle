<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Http;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Alexander <iam.asm89@gmail.com>
 */
interface ResourceOwnerMapInterface
{
    /**
     * Check that resource owner with given name exists.
     */
    public function hasResourceOwnerByName(string $name): bool;

    /**
     * Gets the appropriate resource owner given the name.
     */
    public function getResourceOwnerByName(string $name): ?ResourceOwnerInterface;

    /**
     * Gets the appropriate resource owner for a request.
     */
    public function getResourceOwnerByRequest(Request $request): ?array;

    /**
     * Gets the check path for given resource name.
     */
    public function getResourceOwnerCheckPath(string $name): ?string;

    /**
     * Get all the resource owners.
     *
     * @return array<string, string>
     */
    public function getResourceOwners(): array;
}
