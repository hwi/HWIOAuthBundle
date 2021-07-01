<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle;

use HWI\Bundle\OAuthBundle\DependencyInjection\CompilerPass\ResourceOwnerMapCompilerPass;
use HWI\Bundle\OAuthBundle\DependencyInjection\CompilerPass\SetResourceOwnerServiceNameCompilerPass;
use HWI\Bundle\OAuthBundle\DependencyInjection\HWIOAuthExtension;
use HWI\Bundle\OAuthBundle\DependencyInjection\Security\Factory\OAuthAuthenticatorFactory;
use HWI\Bundle\OAuthBundle\DependencyInjection\Security\Factory\OAuthFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <geoffrey.bachelet@gmail.com>
 */
class HWIOAuthBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $extension SecurityExtension */
        $extension = $container->getExtension('security');

        // Symfony < 5.1 BC layer: support new Authenticator-based security system in Symfony 5.1+
        // and old security system in all Symfony versions.
        if (interface_exists(AuthenticatorFactoryInterface::class)) {
            $extension->addSecurityListenerFactory(new OAuthAuthenticatorFactory());
        } else {
            $extension->addSecurityListenerFactory(new OAuthFactory());
        }

        $container->addCompilerPass(new SetResourceOwnerServiceNameCompilerPass());
        $container->addCompilerPass(new ResourceOwnerMapCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        // return the right extension instead of "auto-registering" it. Now the
        // alias can be hwi_oauth instead of hwi_o_auth..
        if (null === $this->extension) {
            return new HWIOAuthExtension();
        }

        return $this->extension;
    }
}
