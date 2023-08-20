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

use HWI\Bundle\OAuthBundle\DependencyInjection\CompilerPass\EnableRefreshOAuthTokenListenerCompilerPass;
use HWI\Bundle\OAuthBundle\DependencyInjection\CompilerPass\ResourceOwnerCompilerPass;
use HWI\Bundle\OAuthBundle\DependencyInjection\Security\Factory\OAuthAuthenticatorFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
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

        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addAuthenticatorFactory(new OAuthAuthenticatorFactory($this->extension->getFirewallNames()));

        $container->addCompilerPass(new ResourceOwnerCompilerPass());
        $container->addCompilerPass(new EnableRefreshOAuthTokenListenerCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        // return the right extension instead of "auto-registering" it. Now the
        // alias can be hwi_oauth instead of hwi_o_auth.
        return $this->extension ?: $this->extension = $this->createContainerExtension();
    }
}
