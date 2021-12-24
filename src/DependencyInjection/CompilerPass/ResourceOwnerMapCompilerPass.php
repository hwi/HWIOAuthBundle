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

use HWI\Bundle\OAuthBundle\DependencyInjection\HWIOAuthExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add resource ownermaps to the locator and utils.
 */
final class ResourceOwnerMapCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        /** @var HWIOAuthExtension $extension */
        $extension = $container->getExtension('hwi_oauth');
        $firewallNames = $extension->getFirewallNames();

        $locatorDef = $container->getDefinition('hwi_oauth.resource_ownermap_locator');
        $oauthUtilsDef = $container->getDefinition('hwi_oauth.security.oauth_utils');

        foreach ($firewallNames as $firewallName => $_) {
            $resourceOwnerMapRef = new Reference('hwi_oauth.resource_ownermap.'.$firewallName);

            $locatorDef->addMethodCall('set', [$firewallName, $resourceOwnerMapRef]);
            $oauthUtilsDef->addMethodCall('addResourceOwnerMap', [$resourceOwnerMapRef]);
        }
    }
}
