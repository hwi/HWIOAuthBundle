<?php

namespace Knp\OAuthBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class OAuthFactory extends AbstractFactory
{
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId = 'knp_oauth.authentication.provider.oauth.'.$id;

        $container->setDefinition($providerId, new DefinitionDecorator('knp_oauth.authentication.provider.oauth'));
        
        return $providerId;
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId = 'knp_oauth.authentication.entry_point.oauth.'.$id;
        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('knp_oauth.authentication.entry_point.oauth'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument($config['authorization_url'])
            ->addArgument($config['client_id'])
            ->addArgument($config['scope'])
            ->addArgument($config['secret'])
            ->addArgument($config['check_path'])
        ;

        return $entryPointId;
    }

    protected function createListener($container, $id, $config, $userProvider)
    {
        $listenerId = parent::createListener($container, $id, $config, $userProvider);

        $container->getDefinition($listenerId)
            ->addMethodCall('setHttpClient', array(new Reference('buzz.client')))
            ->addMethodCall('setOAuthOptions', array($config))
        ;

        return $listenerId;
    }

    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);

        $node->children()
            ->scalarNode('authorization_url')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('access_token_url')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('client_id')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('scope')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('secret')->cannotBeEmpty()->isRequired()->end()
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