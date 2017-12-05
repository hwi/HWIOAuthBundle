<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\DependencyInjection;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class HWIOAuthExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     * @throws \RuntimeException
     * @throws InvalidConfigurationException
     * @throws \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\OutOfBoundsException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $loader->load('http_client.xml');
        $loader->load('oauth.xml');
        $loader->load('templating.xml');
        $loader->load('twig.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $this->createHttplugClient($container, $config);

        // set current firewall
        if (empty($config['firewall_names'])) {
            throw new InvalidConfigurationException('The child node "firewall_names" at path "hwi_oauth" must be configured.');
        }
        $container->setParameter('hwi_oauth.firewall_names', $config['firewall_names']);

        // set target path parameter
        $container->setParameter('hwi_oauth.target_path_parameter', $config['target_path_parameter']);

        // set use referer parameter
        $container->setParameter('hwi_oauth.use_referer', $config['use_referer']);

        // set failed use referer parameter
        $container->setParameter('hwi_oauth.failed_use_referer', $config['failed_use_referer']);

        // set failed auth path
        $container->setParameter('hwi_oauth.failed_auth_path', $config['failed_auth_path']);

        // set grant rule
        $container->setParameter('hwi_oauth.grant_rule', $config['grant_rule']);

        // setup services for all configured resource owners
        $resourceOwners = array();
        foreach ($config['resource_owners'] as $name => $options) {
            $resourceOwners[$name] = $name;
            $this->createResourceOwnerService($container, $name, $options);
        }
        $container->setParameter('hwi_oauth.resource_owners', $resourceOwners);

        $oauthUtils = $container->getDefinition('hwi_oauth.security.oauth_utils');
        foreach ($config['firewall_names'] as $firewallName) {
            $oauthUtils->addMethodCall('addResourceOwnerMap', array(new Reference('hwi_oauth.resource_ownermap.'.$firewallName)));
        }

        $this->createConnectIntegration($container, $config);

        $container->setAlias('hwi_oauth.user_checker', new Alias('security.user_checker', true));
    }

    /**
     * Creates a resource owner service.
     *
     * @param ContainerBuilder $container The container builder
     * @param string           $name      The name of the service
     * @param array            $options   Additional options of the service
     *
     * @throws InvalidConfigurationException
     * @throws \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function createResourceOwnerService(ContainerBuilder $container, $name, array $options)
    {
        $definitionClassname = $this->getDefinitionClassname();

        // alias services
        if (isset($options['service'])) {
            // set the appropriate name for aliased services, compiler pass depends on it
            $container->setAlias('hwi_oauth.resource_owner.'.$name, new Alias($options['service'], true));

            return;
        }

        $type = $options['type'];
        unset($options['type']);

        // handle external resource owners with given class
        if (isset($options['class'])) {
            if (!is_subclass_of($options['class'], ResourceOwnerInterface::class)) {
                throw new InvalidConfigurationException(sprintf('Class "%s" must implement interface "HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface".', $options['class']));
            }

            $definition = new $definitionClassname('hwi_oauth.abstract_resource_owner.'.$type);
            $definition->setClass($options['class']);
            unset($options['class']);
        } else {
            $definition = new $definitionClassname('hwi_oauth.abstract_resource_owner.'.Configuration::getResourceOwnerType($type));
            $definition->setClass("%hwi_oauth.resource_owner.$type.class%");
        }

        $definition->replaceArgument(2, $options);
        $definition->replaceArgument(3, $name);
        $definition->setPublic(true);

        $container->setDefinition('hwi_oauth.resource_owner.'.$name, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'hwi_oauth';
    }

    /**
     * Check of the connect controllers etc should be enabled.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\BadMethodCallException
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    private function createConnectIntegration(ContainerBuilder $container, array $config)
    {
        $definitionClassname = $this->getDefinitionClassname();

        if (isset($config['connect'])) {
            $container->setParameter('hwi_oauth.connect', true);

            if (isset($config['fosub'])) {
                $container->setParameter('hwi_oauth.fosub_enabled', true);

                $definition = $container->setDefinition('hwi_oauth.user.provider.fosub_bridge', new $definitionClassname('hwi_oauth.user.provider.fosub_bridge.def'));
                $definition->addArgument($config['fosub']['properties']);

                // setup fosub bridge services
                $container->setAlias('hwi_oauth.account.connector', new Alias('hwi_oauth.user.provider.fosub_bridge', true));

                $definition = $container->setDefinition('hwi_oauth.registration.form.handler.fosub_bridge', new $definitionClassname('hwi_oauth.registration.form.handler.fosub_bridge.def'));
                $definition->addArgument($config['fosub']['username_iterations']);

                $container->setAlias('hwi_oauth.registration.form.handler', new Alias('hwi_oauth.registration.form.handler.fosub_bridge', true));

                // enable compatibility with FOSUserBundle 1.3.x and 2.x
                if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
                    $container->setAlias('hwi_oauth.registration.form.factory', new Alias('fos_user.registration.form.factory', true));
                } else {
                    // FOSUser 1.3 BC. To be removed.
                    $definition->setScope('request');

                    $container->setAlias('hwi_oauth.registration.form', new Alias('fos_user.registration.form', true));
                }
            } else {
                $container->setParameter('hwi_oauth.fosub_enabled', false);
            }

            foreach ($config['connect'] as $key => $serviceId) {
                if ('confirmation' === $key) {
                    $container->setParameter('hwi_oauth.connect.confirmation', $config['connect']['confirmation']);

                    continue;
                }

                $container->setAlias('hwi_oauth.'.str_replace('_', '.', $key), new Alias($serviceId, true));
            }
        } else {
            $container->setParameter('hwi_oauth.fosub_enabled', false);
            $container->setParameter('hwi_oauth.connect', false);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function createHttplugClient(ContainerBuilder $container, array $config)
    {
        $httpClientId = $config['http']['client'];
        $httpMessageFactoryId = $config['http']['message_factory'];
        $bundles = $container->getParameter('kernel.bundles');

        if ('httplug.client.default' === $httpClientId && !isset($bundles['HttplugBundle'])) {
            throw new InvalidConfigurationException(
                'You must setup php-http/httplug-bundle to use the default http client service.'
            );
        }
        if ('httplug.message_factory.default' === $httpMessageFactoryId && !isset($bundles['HttplugBundle'])) {
            throw new InvalidConfigurationException(
                'You must setup php-http/httplug-bundle to use the default http message factory service.'
            );
        }

        $container->setAlias('hwi_oauth.http.client', new Alias($config['http']['client'], true));
        $container->setAlias('hwi_oauth.http.message_factory', new Alias($config['http']['message_factory'], true));
    }

    /**
     * @return string
     */
    private function getDefinitionClassname()
    {
        return class_exists(ChildDefinition::class) ? ChildDefinition::class : DefinitionDecorator::class;
    }
}
