<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

// BC symfony 4.4
class_exists(ContainerConfigurator::class);

if (!\function_exists(__NAMESPACE__.'\\service')) {
    function service($class): ReferenceConfigurator
    {
        /* @phpstan-ignore-next-line function ref not found */
        return ref($class);
    }
}

namespace HWI\Bundle\OAuthBundle\DependencyInjection;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
final class HWIOAuthExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     * @throws \RuntimeException
     * @throws InvalidConfigurationException
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @throws ServiceNotFoundException
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $loader->load('controller.php');
        $loader->load('oauth.php');
        $loader->load('resource_owners.php');
        $loader->load('templating.php');
        $loader->load('twig.php');
        $loader->load('util.php');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        // set current firewall
        if (empty($config['firewall_names'])) {
            throw new InvalidConfigurationException('The child node "firewall_names" at path "hwi_oauth" must be configured.');
        }
        $container->setParameter('hwi_oauth.firewall_names', $config['firewall_names']);

        // set target path parameter
        $container->setParameter('hwi_oauth.target_path_parameter', $config['target_path_parameter']);

        // set target path domains whitelist parameter
        $container->setParameter('hwi_oauth.target_path_domains_whitelist', $config['target_path_domains_whitelist']);

        // set use referer parameter
        $container->setParameter('hwi_oauth.use_referer', $config['use_referer']);

        // set failed use referer parameter
        $container->setParameter('hwi_oauth.failed_use_referer', $config['failed_use_referer']);

        // set failed auth path
        $container->setParameter('hwi_oauth.failed_auth_path', $config['failed_auth_path']);

        // set grant rule
        $container->setParameter('hwi_oauth.grant_rule', $config['grant_rule']);

        // setup services for all configured resource owners
        $resourceOwners = [];
        foreach ($config['resource_owners'] as $name => $options) {
            $resourceOwners[$name] = $name;
            $this->createResourceOwnerService($container, $name, $options);
        }
        $container->setParameter('hwi_oauth.resource_owners', $resourceOwners);

        $oauthUtils = $container->getDefinition('hwi_oauth.security.oauth_utils');
        foreach ($config['firewall_names'] as $firewallName) {
            $oauthUtils->addMethodCall('addResourceOwnerMap', [new Reference('hwi_oauth.resource_ownermap.'.$firewallName)]);
        }

        $this->createConnectIntegration($container, $config);
    }

    /**
     * Creates a resource owner service.
     *
     * @param ContainerBuilder $container The container builder
     * @param string           $name      The name of the service
     * @param array            $options   Additional options of the service
     *
     * @throws InvalidConfigurationException
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     */
    public function createResourceOwnerService(ContainerBuilder $container, string $name, array $options): void
    {
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

            $definition = new Definition($options['class']);
            unset($options['class']);
        } else {
            $definition = new Definition("%hwi_oauth.resource_owner.$type.class%");
        }

        $definition->setArgument('$httpClient', new Reference('hwi_oauth.http_client'));
        $definition->setArgument('$httpUtils', new Reference('security.http_utils'));
        $definition->setArgument('$options', $options);
        $definition->setArgument('$name', $name);
        $definition->setArgument('$storage', new Reference('hwi_oauth.storage.session'));
        $definition->addTag('hwi_oauth.resource_owner', ['resource-name' => $name]);

        $container->setDefinition('hwi_oauth.resource_owner.'.$name, $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'hwi_oauth';
    }

    /**
     * Check of the connect controllers etc should be enabled.
     *
     * @throws BadMethodCallException
     * @throws InvalidArgumentException
     */
    private function createConnectIntegration(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('hwi_oauth.connect', isset($config['connect']));
        $container->setParameter('hwi_oauth.connect.confirmation', $config['connect']['confirmation'] ?? false);
        $container->setParameter('hwi_oauth.connect.registration_form', $config['connect']['registration_form'] ?? null);

        if (isset($config['connect']['account_connector'])) {
            $container->setAlias('hwi_oauth.account.connector', new Alias($config['connect']['account_connector'], true));
        }

        if (isset($config['connect']['registration_form_handler'])) {
            $container->setAlias('hwi_oauth.registration.form.handler', new Alias($config['connect']['registration_form_handler'], true));
        }
    }
}
