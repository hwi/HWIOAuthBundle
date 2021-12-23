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
 * For Symfony 4.4 only
 * Adds already registered by OAuthFactory RefreshAccessTokenListenerOld to security.firewall.map.context.
 * It's taking control immediately after token was passed from session to token storage.
 */
class RefreshOAuthTokenCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $listenerId => $listenerDef) {
            if (0 === strpos($listenerId, 'hwi_oauth.context_listener.token_refresher.')) {
                $firewallName = substr($listenerId, 43);
                $firewallMapContextId = 'security.firewall.map.context.'.$firewallName;

                if ($container->has($firewallMapContextId)) {
                    $firewallMapContextDef = $container->getDefinition($firewallMapContextId);
                    /* @var IteratorArgument $listenersIter */
                    $listenerIter = $firewallMapContextDef->getArgument(0);

                    $listenerRefs = $listenerIter->getValues();
                    // add listener after security.context_listener.X
                    for ($pos = 0; $pos < \count($listenerRefs); ++$pos) {
                        if (0 === strpos($listenerRefs[$pos], 'security.context_listener.')) {
                            array_splice($listenerRefs, $pos + 1, 0, [new Reference($listenerId)]);
                            break;
                        }
                    }
                    $listenerIter->setValues($listenerRefs);
                }
            }
        }
    }
}
