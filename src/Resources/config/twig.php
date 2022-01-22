<?php

declare(strict_types=1);

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use HWI\Bundle\OAuthBundle\Twig\Extension\OAuthExtension;
use HWI\Bundle\OAuthBundle\Twig\Extension\OAuthRuntime;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('hwi_oauth.twig.extension.oauth', OAuthExtension::class)
        ->tag('twig.extension');

    $services->set('hwi_oauth.twig.extension.oauth.runtime', OAuthRuntime::class)
        ->args([
            service('hwi_oauth.security.oauth_utils'),
            service('request_stack'),
        ])
        ->tag('twig.runtime');
};
