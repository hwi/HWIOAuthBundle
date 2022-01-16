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

use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @deprecated For Symfony 4.4 only
 *
 * Adds already registered by OAuthFactory RefreshAccessTokenListenerOld to security.firewall.map.context.
 * It's taking control immediately after token was passed from session to token storage.
 */
final class RefreshOAuthTokenCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $listenerId => $listenerDef) {
            if (0 !== strpos($listenerId, 'hwi_oauth.context_listener.token_refresher.')) {
                continue;
            }

            // Cut 'hwi_oauth.context_listener.token_refresher.'
            $firewallName = substr($listenerId, 43);
            $firewallMapContextId = 'security.firewall.map.context.'.$firewallName;

            if (!$container->has($firewallMapContextId)) {
                continue;
            }

            $firewallMapContextDef = $container->getDefinition($firewallMapContextId);
            /* @var IteratorArgument $listenersIter */
            $listenerIter = $firewallMapContextDef->getArgument(0);

            $listenerRefs = $listenerIter->getValues();
            // add listener after security.context_listener.X
            foreach ($listenerRefs as $pos => $posValue) {
                if (0 === strpos($posValue, 'security.context_listener.')) {
                    array_splice($listenerRefs, $pos + 1, 0, [new Reference($listenerId)]);
                    break;
                }
            }
            $listenerIter->setValues($listenerRefs);
        }
    }
}
