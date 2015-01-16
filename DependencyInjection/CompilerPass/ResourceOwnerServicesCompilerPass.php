<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register ResourceOwner tagged services
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class ResourceOwnerServiceCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('hwi_oauth.resource_owner') as $id => $tag) {
            $definition = $container->findDefinition($id);

            $definition->addMethodCall('setName', array($tag[0]['alias']));
            $definition->addMethodCall('setOptions', array($container->getParameter("hwi_oauth.resource_owner.{$tag[0]['alias']}.parameters")));
        }
    }
}

