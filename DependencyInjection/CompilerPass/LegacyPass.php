<?php
/**
 * Created by PhpStorm.
 * User: Pavel Batanov <pavel@batanov.me>
 * Date: 30.01.2015
 * Time: 19:22
 */

namespace HWI\Bundle\OAuthBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class LegacyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('hwi_oauth.security.oauth_utils')) {
            return;
        }

        $definition = $container->getDefinition('hwi_oauth.security.oauth_utils');

        if ($container->hasDefinition('security.authorization_checker')) {
            $definition->replaceArgument(1, null);
        }
    }
}
