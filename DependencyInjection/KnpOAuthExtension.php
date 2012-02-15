<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\Config\Definition\Processor,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * KnpOAuthExtension
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @auther Alexander <iam.asm89@gmail.com>
 */
class KnpOAuthExtension extends Extension
{
    /**
     * @{inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $loader->load('oauth.xml');

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        // setup services for all configured resource owners
        foreach ($config['resource_owners'] as $name => $options) {
            $type = $options['type'];
            unset($options['type']);
            $this->createResourceOwnerService($container, $name, $type, $options);
        }
    }

    /**
     * Creates a resource owner service.
     *
     * @param ContainerBuilder $container The container builder
     * @param string           $name      The name of the service
     * @param string           $type      The type of the service
     * @param array            $options   Additional options of the service
     */
    public function createResourceOwnerService(ContainerBuilder $container, $name, $type, array $options)
    {
        $container
            ->register('knp_oauth.resource_owner.'.$name, '%knp_oauth.resource_owner.'.$type.'.class%')
            ->addArgument(new Reference('buzz.client'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'knp_oauth';
    }
}
