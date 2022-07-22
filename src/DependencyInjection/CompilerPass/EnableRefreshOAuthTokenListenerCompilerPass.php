<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\DependencyInjection\CompilerPass;

use HWI\Bundle\OAuthBundle\DependencyInjection\HWIOAuthExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EnableRefreshOAuthTokenListenerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @var HWIOAuthExtension $extension */
        $extension = $container->getExtension('hwi_oauth');
        if (!$extension->isRefreshTokenListenerEnabled()) {
            return;
        }

        foreach ($extension->getFirewallNames() as $firewallName => $_) {
            $container->findDefinition('hwi_oauth.context_listener.token_refresher.'.$firewallName)
                ->addMethodCall('enable');
        }
    }
}
