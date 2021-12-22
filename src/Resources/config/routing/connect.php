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

use HWI\Bundle\OAuthBundle\Controller\ConnectController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('hwi_oauth_connect_service', '/service/{service}')
        ->controller([ConnectController::class, 'connectServiceAction'])
        ->methods(['GET', 'POST']);

    $routes->add('hwi_oauth_connect_registration', '/registration/{key}')
        ->controller([ConnectController::class, 'registrationAction'])
        ->methods(['GET', 'POST']);
};
