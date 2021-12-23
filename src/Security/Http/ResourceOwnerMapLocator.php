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

/**
 * Find resource owner maps based on firewall id.
 */
final class ResourceOwnerMapLocator
{
    /**
     * @var array<string, ResourceOwnerMapInterface>
     */
    private array $resourceOwnerMaps = [];

    public function set(string $firewallName, ResourceOwnerMapInterface $resourceOwnerMap): void
    {
        $this->resourceOwnerMaps[$firewallName] = $resourceOwnerMap;
    }

    public function has(string $firewallName): bool
    {
        return isset($this->resourceOwnerMaps[$firewallName]);
    }

    public function get(string $firewallName): ResourceOwnerMapInterface
    {
        return $this->resourceOwnerMaps[$firewallName];
    }

    /**
     * @return array<string, ResourceOwnerMapInterface>
     */
    public function getResourceOwnerMaps(): array
    {
        return $this->resourceOwnerMaps;
    }

    /**
     * @return string[]
     */
    public function getFirewallNames(): array
    {
        return array_keys($this->resourceOwnerMaps);
    }
}
