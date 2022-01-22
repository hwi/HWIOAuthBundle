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

use HWI\Bundle\OAuthBundle\Controller\Connect\ConnectController;
use HWI\Bundle\OAuthBundle\Controller\Connect\RegisterController;
use HWI\Bundle\OAuthBundle\Controller\LoginController;
use HWI\Bundle\OAuthBundle\Controller\RedirectToServiceController;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ConnectController::class)
        ->public()
        ->arg('$oauthUtils', service('hwi_oauth.security.oauth_utils'))
        ->arg('$resourceOwnerMapLocator', service('hwi_oauth.resource_ownermap_locator'))
        ->arg('$requestStack', service('request_stack'))
        ->arg('$dispatcher', service('event_dispatcher'))
        ->arg('$tokenStorage', service('security.token_storage'))
        ->arg('$userChecker', service('hwi_oauth.user_checker'))
        ->arg('$authorizationChecker', service('security.authorization_checker'))
        ->arg('$formFactory', service('form.factory'))
        ->arg('$twig', service('twig'))
        ->arg('$router', service('router'))
        ->arg('$grantRule', '%hwi_oauth.grant_rule%')
        ->arg('$failedUseReferer', '%hwi_oauth.failed_use_referer%')
        ->arg('$failedAuthPath', '%hwi_oauth.failed_auth_path%')
        ->arg('$enableConnectConfirmation', '%hwi_oauth.connect.confirmation%')
        ->arg('$accountConnector', service('hwi_oauth.account.connector')->nullOnInvalid());

    $services->set(RegisterController::class)
        ->public()
        ->arg('$resourceOwnerMapLocator', service('hwi_oauth.resource_ownermap_locator'))
        ->arg('$requestStack', service('request_stack'))
        ->arg('$dispatcher', service('event_dispatcher'))
        ->arg('$tokenStorage', service('security.token_storage'))
        ->arg('$userChecker', service('hwi_oauth.user_checker'))
        ->arg('$authorizationChecker', service('security.authorization_checker'))
        ->arg('$formFactory', service('form.factory'))
        ->arg('$twig', service('twig'))
        ->arg('$grantRule', '%hwi_oauth.grant_rule%')
        ->arg('$registrationForm', '%hwi_oauth.connect.registration_form%')
        ->arg('$accountConnector', service('hwi_oauth.account.connector')->nullOnInvalid())
        ->arg('$formHandler', service('hwi_oauth.registration.form.handler')->nullOnInvalid());

    $services->set(LoginController::class)
        ->public()
        ->args([
            service('security.authentication_utils'),
            service('router'),
            service('security.authorization_checker'),
            service('request_stack'),
            service('twig'),
            '%hwi_oauth.connect%',
            '%hwi_oauth.grant_rule%',
        ]);

    $services->set(RedirectToServiceController::class)
        ->public()
        ->args([
            service('hwi_oauth.security.oauth_utils'),
            service('hwi_oauth.util.domain_whitelist'),
            service('hwi_oauth.resource_ownermap_locator'),
            '%hwi_oauth.target_path_parameter%',
            '%hwi_oauth.failed_use_referer%',
            '%hwi_oauth.use_referer%',
        ]);

    $services->alias('hwi_oauth.user_checker', 'security.user_checker')
        ->public();
};
