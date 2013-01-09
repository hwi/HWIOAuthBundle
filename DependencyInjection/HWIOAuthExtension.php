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

use Symfony\Component\Config\FileLocator,
    Symfony\Component\Config\Definition\Processor,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\HttpKernel\DependencyInjection\Extension;

use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * HWIOAuthExtension
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @auther Alexander <iam.asm89@gmail.com>
 */
class HWIOAuthExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $loader->load('oauth.xml');
        $loader->load('templating.xml');
        $loader->load('twig.xml');
        $loader->load('buzz.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        // setup buzz client settings
        $httpClient = $container->getDefinition('buzz.client');
        $httpClient->addMethodCall('setVerifyPeer', array($config['http_client']['verify_peer']));
        $httpClient->addMethodCall('setTimeout', array($config['http_client']['timeout']));
        $httpClient->addMethodCall('setMaxRedirects', array($config['http_client']['max_redirects']));
        $httpClient->addMethodCall('setIgnoreErrors', array($config['http_client']['ignore_errors']));
        $container->setDefinition('hwi_oauth.http_client', $httpClient);

        // set current firewall
        $container->setParameter('hwi_oauth.firewall_name', $config['firewall_name']);

        // setup services for all configured resource owners
        $resourceOwners = array();
        foreach ($config['resource_owners'] as $name => $options) {
            $resourceOwners[] = $name;
            $this->createResourceOwnerService($container, $name, $options);
        }
        $container->setParameter('hwi_oauth.resource_owners', $resourceOwners);

        if (isset($config['fosub'])) {
            $container
                ->setDefinition('hwi_oauth.user.provider.fosub_bridge', new DefinitionDecorator('hwi_oauth.user.provider.fosub_bridge.def'))
                ->addArgument($config['fosub']['properties']);
        }

        // check of the connect controllers etc should be enabled
        if (isset($config['connect'])) {
            $container->setParameter('hwi_oauth.connect', true);

            if (isset($config['fosub'])) {
                // setup fosub bridge services
                $container->setAlias('hwi_oauth.account.connector', 'hwi_oauth.user.provider.fosub_bridge');

                $container
                    ->setDefinition('hwi_oauth.registration.form.handler.fosub_bridge', new DefinitionDecorator('hwi_oauth.registration.form.handler.fosub_bridge.def'))
                    ->addArgument($config['fosub']['username_iterations'])
                    ->setScope('request');

                $container->setAlias('hwi_oauth.registration.form.handler', 'hwi_oauth.registration.form.handler.fosub_bridge');

                // enable compatibility with FOSUserBundle 1.3.x and 2.x
                if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
                    $container->setAlias('hwi_oauth.registration.form.factory', 'fos_user.registration.form.factory');
                } else {
                    $container->setAlias('hwi_oauth.registration.form', 'fos_user.registration.form');
                }
            }

            foreach ($config['connect'] as $key => $serviceId) {
                 $container->setAlias('hwi_oauth.'.str_replace('_', '.', $key), $serviceId);
            }

            // setup custom services
        } else {
            $container->setParameter('hwi_oauth.connect', false);
        }

        $container->setAlias('hwi_oauth.user_checker', 'security.user_checker');
    }

    /**
     * Creates a resource owner service.
     *
     * @param ContainerBuilder $container The container builder
     * @param string           $name      The name of the service
     * @param array            $options   Additional options of the service
     */
    public function createResourceOwnerService(ContainerBuilder $container, $name, array $options)
    {
        // alias services
        if (isset($options['service'])) {
            $container
                ->setAlias('hwi_oauth.resource_owner.'.$name, $options['service']);

            // set the appropriate name for aliased services
            // TODO fix this. It cannot work as the service definition cannot be accessed
            // and this id is an alias anyway, not a definition.
            $resourceOwnerDefinition = $container->getDefinition('hwi_oauth.resource_owner.'.$name);
            $resourceOwnerDefinition->addMethodCall('setName', array($name));
        } else {
            $type = $options['type'];
            unset($options['type']);

            $paths = array();
            if (isset($options['paths'])) {
                $paths = $options['paths'];
                unset($options['paths']);
            }

            $definition = new DefinitionDecorator('hwi_oauth.abstract_resource_owner.'.$type);
            $container->setDefinition('hwi_oauth.resource_owner.'.$name, $definition);
            $definition->replaceArgument(2, $options)
                ->replaceArgument(3, $name);

            if (!empty($paths)) {
                $definition->addMethodCall('addPaths', array($paths));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'hwi_oauth';
    }
}
