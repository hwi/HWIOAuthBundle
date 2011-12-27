<?php

/*
 * This file is part of the KnpOAuthBundle package.
 *
 * (c) KnpLabs <hello@knplabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\OAuthBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle,
    Symfony\Component\DependencyInjection\ContainerBuilder;

use Knp\Bundle\OAuthBundle\DependencyInjection\Security\Factory\OAuthFactory,
    Knp\Bundle\OAuthBundle\Security\Core\UserProvider\EntityFactory;

/**
 * KnpOAuthBundle
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class KnpOAuthBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new OAuthFactory());
        $extension->addUserProviderFactory(new EntityFactory('entity', 'doctrine.orm.security.user.provider'));
    }
}