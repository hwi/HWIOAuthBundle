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

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * HWIOAuthExtension
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
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
        $loader->load('http_client.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        // setup http client settings
        $httpClient = $container->getDefinition('hwi_oauth.http_client');
        $httpClient->addMethodCall('setVerifyPeer', array($config['http_client']['verify_peer']));
        $httpClient->addMethodCall('setTimeout', array($config['http_client']['timeout']));
        $httpClient->addMethodCall('setMaxRedirects', array($config['http_client']['max_redirects']));
        $httpClient->addMethodCall('setIgnoreErrors', array($config['http_client']['ignore_errors']));
        if (isset($config['http_client']['proxy']) && $config['http_client']['proxy'] != '') {
            $httpClient->addMethodCall('setProxy', array($config['http_client']['proxy']));
        }

        // set current firewall
        if (empty($config['firewall_names']) && !isset($config['firewall_name'])) {
            throw new InvalidConfigurationException('The child node "firewall_name" or "firewall_names" at path "hwi_oauth" must be configured.');
        } elseif (!empty($config['firewall_names']) && isset($config['firewall_name'])) {
            $config['firewall_names'] = array_merge(array($config['firewall_name']), $config['firewall_names']);
        } elseif (empty($config['firewall_names']) && isset($config['firewall_name'])) {
            @trigger_error('The child node "firewall_name" at path "hwi_oauth" is deprecated since version 0.4.0 and will be removed in version 0.5.0. Use "firewall_names" instead.', E_USER_DEPRECATED);
            $config['firewall_names'] = array($config['firewall_name']);
        }
        $container->setParameter('hwi_oauth.firewall_names', $config['firewall_names']);

        // set target path parameter
        $container->setParameter('hwi_oauth.target_path_parameter', $config['target_path_parameter']);

        // set use referer parameter
        $container->setParameter('hwi_oauth.use_referer', $config['use_referer']);

        // set failed auth path
        $container->setParameter('hwi_oauth.failed_auth_path', $config['failed_auth_path']);
        
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

        // Symfony <2.6 BC
        // Go back to basic xml config after
        if (interface_exists('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface')) {
            $oauthUtils->replaceArgument(1, new Reference('security.authorization_checker'));
        } else {
            $oauthUtils->replaceArgument(1, new Reference('security.context'));
        }

        if (isset($config['fosub'])) {
            $container
                ->setDefinition('hwi_oauth.user.provider.fosub_bridge', new DefinitionDecorator('hwi_oauth.user.provider.fosub_bridge.def'))
                ->addArgument($config['fosub']['properties'])
            ;
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
                    ->setScope('request')
                ;

                $container->setAlias('hwi_oauth.registration.form.handler', 'hwi_oauth.registration.form.handler.fosub_bridge');

                // enable compatibility with FOSUserBundle 1.3.x and 2.x
                if (interface_exists('FOS\UserBundle\Form\Factory\FactoryInterface')) {
                    $container->setAlias('hwi_oauth.registration.form.factory', 'fos_user.registration.form.factory');
                } else {
                    $container->setAlias('hwi_oauth.registration.form', 'fos_user.registration.form');
                }
            }

            foreach ($config['connect'] as $key => $serviceId) {
                if ('confirmation' === $key) {
                    $container->setParameter('hwi_oauth.connect.confirmation', $config['connect']['confirmation']);

                    continue;
                }

                $container->setAlias('hwi_oauth.'.str_replace('_', '.', $key), $serviceId);
            }

            // setup custom services
        } else {
            $container->setParameter('hwi_oauth.connect', false);
        }

        $container->setParameter('hwi_oauth.templating.engine', $config['templating_engine']);

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
            // set the appropriate name for aliased services, compiler pass depends on it
            $container->setAlias('hwi_oauth.resource_owner.'.$name, $options['service']);
        } else {
            $type = $options['type'];
            unset($options['type']);

            $definition = new DefinitionDecorator('hwi_oauth.abstract_resource_owner.'.Configuration::getResourceOwnerType($type));
            $definition->setClass("%hwi_oauth.resource_owner.$type.class%");
            $container->setDefinition('hwi_oauth.resource_owner.'.$name, $definition);
            $definition
                ->replaceArgument(2, $options)
                ->replaceArgument(3, $name)
            ;
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
