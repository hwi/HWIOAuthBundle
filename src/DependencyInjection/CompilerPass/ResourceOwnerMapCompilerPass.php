<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add resource ownermaps to the locator.
 */
final class ResourceOwnerMapCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->getDefinition('hwi_oauth.resource_ownermap_locator');

        foreach ($container->getParameter('hwi_oauth.firewall_names') as $firewallId) {
            $def->addMethodCall('add', [$firewallId, new Reference('hwi_oauth.resource_ownermap.'.$firewallId)]);
        }
    }
}
