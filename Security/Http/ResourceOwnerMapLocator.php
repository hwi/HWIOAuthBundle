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
     * @var array
     */
    private $resourceOwnerMaps = [];

    /**
     * @param string                    $firewallId
     * @param ResourceOwnerMapInterface $resourceOwnerMap
     */
    public function add(string $firewallId, ResourceOwnerMapInterface $resourceOwnerMap): void
    {
        $this->resourceOwnerMaps[$firewallId] = $resourceOwnerMap;
    }

    /**
     * @param string $firewallId
     *
     * @return bool
     */
    public function has(string $firewallId): bool
    {
        return isset($this->resourceOwnerMaps[$firewallId]);
    }

    /**
     * @param string $firewallId
     *
     * @return ResourceOwnerMapInterface
     */
    public function get(string $firewallId): ResourceOwnerMapInterface
    {
        return $this->resourceOwnerMaps[$firewallId];
    }
}
