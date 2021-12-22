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

use HWI\Bundle\OAuthBundle\Controller\RedirectToServiceController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('hwi_oauth_service_redirect', '/{service}')
        ->controller([RedirectToServiceController::class, 'redirectToServiceAction'])
        ->methods(['GET']);
};
