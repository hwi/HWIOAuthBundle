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
            ->addArgument($config['entry_point'])
            ->addArgument($config['client_id'])
            ->addArgument($config['scope'])
            ->addArgument($config['secret'])
            ->addArgument($config['check_path'])
        ;

        return $entryPointId;
    }

    public function addConfiguration(NodeDefinition $node)
    {
        parent::addConfiguration($node);

        $node->children()
            ->scalarNode('entry_point')->cannotBeEmpty()->isRequired()->end()
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