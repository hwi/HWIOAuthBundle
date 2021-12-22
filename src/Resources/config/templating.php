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

use HWI\Bundle\OAuthBundle\Templating\Helper\OAuthHelper;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('hwi_oauth.templating.helper.oauth', OAuthHelper::class)
        ->args([
            service('hwi_oauth.security.oauth_utils'),
            service('request_stack'),
        ])
        ->tag('templating.helper', ['alias' => 'oauth']);
};
