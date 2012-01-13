<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\DefinitionDecorator,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\Config\Definition\Builder\NodeDefinition;

/**
 * OAuthFactory
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class OAuthFactory extends AbstractFactory
{
    /**
     * Creates an OAuth provider for a given firewall
     *
     * @param Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $id The firewall id
     * @param array $config The firewall config
     * @return string The OAuth provider service id
     */
    protected function createOAuthProvider(ContainerBuilder $container, $id, $config)
    {
        if (false !== strpos($config['oauth_provider'], '.')) {
            $baseOAuthProviderId = $config['oauth_provider'];
        } else {
            $baseOAuthProviderId = 'knp_oauth.security.oauth.'.$config['oauth_provider'].'_provider';
        }

        $oauthProviderId = $baseOAuthProviderId.'.'.$id;

        $container
            ->setDefinition($oauthProviderId, new DefinitionDecorator($baseOAuthProviderId))
            ->addArgument(new Reference('buzz.client'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument($config);

        return $oauthProviderId;
    }

    /**
     * {@inheritDoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId      = 'knp_oauth.authentication.provider.oauth.'.$id;
        $oauthProviderId = $this->createOAuthProvider($container, $id, $config);

        $container
            ->setDefinition($providerId, new DefinitionDecorator('knp_oauth.authentication.provider.oauth'))
            ->addArgument(new Reference($userProviderId))
            ->addArgument(new Reference($oauthProviderId));

        return $providerId;
    }

    /**
     * {@inheritDoc}
     */
    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId    = 'knp_oauth.authentication.entry_point.oauth.'.$id;
        $oauthProviderId = $this->createOAuthProvider($container, $id, $config);

        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('knp_oauth.authentication.entry_point.oauth'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument(new Reference($oauthProviderId))
            ->addArgument($config['check_path'])
            ->addArgument($config['login_path'])
        ;

        return $entryPointId;
    }

    /**
     * {@inheritDoc}
     */
    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId      = parent::createListener($container, $id, $config, $userProvider);
        $oauthProviderId = $this->createOAuthProvider($container, $id, $config);

        $container->getDefinition($listenerId)
            ->addMethodCall('setOAuthProvider', array(new Reference($oauthProviderId)))
        ;

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
            ->scalarNode('oauth_provider')
                ->defaultValue('oauth')
            ->end()
            ->scalarNode('authorization_url')
                ->defaultNull()
            ->end()
            ->scalarNode('access_token_url')
                ->defaultNull()
            ->end()
            ->scalarNode('infos_url')
                ->defaultNull()
            ->end()
            ->scalarNode('username_path')
                ->defaultNull()
            ->end()
            ->scalarNode('client_id')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
            ->scalarNode('scope')
                ->isRequired()
            ->end()
            ->scalarNode('secret')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function getListenerId()
    {
      return 'knp_oauth.authentication.listener.oauth';
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