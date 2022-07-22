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

use HWI\Bundle\OAuthBundle\DependencyInjection\Configuration;
use HWI\Bundle\OAuthBundle\DependencyInjection\HWIOAuthExtension;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Registers "hwi_oauth.resource_owner.$type.class" Parameters and checks resource owner configurations, whether given
 * type exists (Apps can add own ResourceOwners).
 *
 * Adds resource owner maps to the locator and utils.
 */
final class ResourceOwnerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $this->registerResourceOwnerTypeClassParameters($container);
        $this->addResourceOwnerMapToLocatorAndUtils($container);
    }

    private function registerResourceOwnerTypeClassParameters(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('hwi_oauth.resource_owner') as $serviceId => $_) {
            $definition = $container->findDefinition($serviceId);
            Configuration::registerResourceOwner($definition->getClass());
        }

        foreach (Configuration::getResourceOwnerTypesClassMap() as $type => $resourceOwnerClass) {
            $parameterName = "hwi_oauth.resource_owner.$type.class";
            if (!$container->hasParameter($parameterName)) {
                $container->setParameter($parameterName, $resourceOwnerClass);
            }
        }

        // Check whether resource owner set with parameter '%hwi_oauth.resource_owner.[type].class%' type exists
        /** @var ServiceLocator $locator */
        $locator = $container->get('hwi_oauth.resource_owners.locator');

        foreach ($locator->getProvidedServices() as $resourceOwnerName => $_) {
            try {
                $definition = $container->findDefinition('hwi_oauth.resource_owner.'.$resourceOwnerName);
            } catch (ServiceNotFoundException $e) {
                // Resource owner defined with "options.service"
                continue;
            }

            $resourceOwnerClass = $definition->getClass();

            // Check whether a ResourceOwner class exists only if resource owner was set by its "options.type"
            if (false === preg_match('~^%(?P<parameter>hwi_oauth.resource_owner.(?P<type>.+).class)%$~', $resourceOwnerClass, $match)) {
                return;
            }

            if (!($match['type'] ?? null)) {
                continue;
            }

            if (!Configuration::isResourceOwnerSupported($match['type'])) {
                $e = new \InvalidArgumentException(sprintf('Unknown resource owner type "%s"', $match['type']));

                throw new InvalidConfigurationException(sprintf('Invalid configuration for path "hwi_oauth.resource_owners.%s.type": %s', $resourceOwnerName, $e->getMessage()), $e->getCode(), $e);
            }
        }
    }

    private function addResourceOwnerMapToLocatorAndUtils(ContainerBuilder $container): void
    {
        /** @var HWIOAuthExtension $extension */
        $extension = $container->getExtension('hwi_oauth');

        $locatorDef = $container->findDefinition('hwi_oauth.resource_ownermap_locator');
        $oauthUtilsDef = $container->findDefinition('hwi_oauth.security.oauth_utils');

        foreach ($extension->getFirewallNames() as $firewallName => $_) {
            $resourceOwnerMapId = 'hwi_oauth.resource_ownermap.'.$firewallName;

            $container->findDefinition($resourceOwnerMapId)
                ->setArgument('$locator', new Reference('hwi_oauth.resource_owners.locator'));

            $resourceOwnerMapRef = new Reference($resourceOwnerMapId);

            $locatorDef->addMethodCall('set', [$firewallName, $resourceOwnerMapRef]);
            $oauthUtilsDef->addMethodCall('addResourceOwnerMap', [$firewallName, $resourceOwnerMapRef]);
        }
    }
}
