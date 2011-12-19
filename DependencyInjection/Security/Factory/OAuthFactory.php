<?php

namespace Knp\OAuthBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class OAuthFactory extends AbstractFactory
{
    protected function createOAuthProvider(ContainerBuilder $container, $id, $config)
    {
        $oauthProviderId = 'knp_oauth.security.oauth.oauth_provider'.$id;

        $container
            ->setDefinition($oauthProviderId, new DefinitionDecorator('knp_oauth.security.oauth.oauth_provider'))
            ->addArgument(new Reference('buzz.client'))
            ->addArgument($config);

        return $oauthProviderId;
    }

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

    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId    = 'knp_oauth.authentication.entry_point.oauth.'.$id;
        $oauthProviderId = $this->createOAuthProvider($container, $id, $config);

        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('knp_oauth.authentication.entry_point.oauth'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument(new Reference($oauthProviderId))
            ->addArgument($config['check_path'])
        ;

        return $entryPointId;
    }

    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId      = parent::createListener($container, $id, $config, $userProvider);
        $oauthProviderId = $this->createOAuthProvider($container, $id, $config);

        $container->getDefinition($listenerId)
            ->addMethodCall('setOAuthProvider', array(new Reference($oauthProviderId)))
        ;

        return $listenerId;
    }

    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);

        $builder = $node->children();

        $builder
            ->scalarNode('oauth_provider')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
            ->scalarNode('authorization_url')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
            ->scalarNode('access_token_url')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
            ->scalarNode('infos_url')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
            ->scalarNode('client_id')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
            ->scalarNode('scope')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
            ->scalarNode('secret')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
            ->scalarNode('username_path')
                ->cannotBeEmpty()
                ->isRequired()
            ->end()
        ;
    }


    protected function getListenerId()
    {
      return 'knp_oauth.authentication.listener.oauth';
    }

    public function getKey()
    {
        return 'oauth';
    }

    public function getPosition()
    {
        return 'http';
    }
}