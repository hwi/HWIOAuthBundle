<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle\Security\Core\UserProvider;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\DefinitionDecorator,
    Symfony\Component\Config\Definition\Builder\NodeDefinition,
    Symfony\Bridge\Doctrine\DependencyInjection\Security\UserProvider\EntityFactory as BaseEntityFactory;

/**
 * EntityFactory
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class EntityFactory extends BaseEntityFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $id, $config)
    {
        $container
            ->setDefinition($id, new DefinitionDecorator('knp_oauth.user.provider.entity'))
            ->addArgument($config['class'])
            ->addArgument($config['property'])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'oauth_entity';
    }
}