<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\DefinitionDecorator,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * OAuthFactory
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class OAuthFactory extends AbstractFactory
{
    /**
     * Gets the reference to the appropriate resource owner service.
     *
     * @param array $config
     *
     * @return Reference
     */
    protected function getResourceOwnerReference(array $config)
    {
        if (false !== strpos($config['resource_owner'], '.')) {
            return new Reference($config['resource_owner']);
        }

        return new Reference('hwi_oauth.resource_owner.'.$config['resource_owner']);
    }

    /**
     * {@inheritDoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId      = 'hwi_oauth.authentication.provider.oauth.'.$id;

        $container
            ->setDefinition($providerId, new DefinitionDecorator('hwi_oauth.authentication.provider.oauth'))
            ->addArgument(new Reference($userProviderId))
            ->addArgument($this->getResourceOwnerReference($config));

        return $providerId;
    }

    /**
     * {@inheritDoc}
     */
    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId    = 'hwi_oauth.authentication.entry_point.oauth.'.$id;

        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('hwi_oauth.authentication.entry_point.oauth'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument($this->getResourceOwnerReference($config))
            ->addArgument($config['check_path'])
            ->addArgument($config['login_path']);

        return $entryPointId;
    }

    /**
     * {@inheritDoc}
     */
    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId      = parent::createListener($container, $id, $config, $userProvider);

        $container->getDefinition($listenerId)
            ->addMethodCall('setResourceOwner', array($this->getResourceOwnerReference($config)))
            ->addMethodCall('setCheckPath', array($config['check_path']));

        return $listenerId;
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);

        $builder = $node->children();

        $builder
            ->scalarNode('resource_owner')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
            ->scalarNode('check_path')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
            ->scalarNode('login_path')
                ->cannotBeEmpty()
                ->isRequired()
            ->end();
    }

    /**
     * {@inheritDoc}
     */
    protected function getListenerId()
    {
      return 'hwi_oauth.authentication.listener.oauth';
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'oauth';
    }

    /**
     * {@inheritDoc}
     */
    public function getPosition()
    {
        return 'http';
    }
}
