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

use HWI\Bundle\OAuthBundle\OAuth\RequestDataStorage\SessionStorage;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Provider\OAuthProvider;
use HWI\Bundle\OAuthBundle\Security\Core\User\EntityUserProvider;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider;
use HWI\Bundle\OAuthBundle\Security\Http\EntryPoint\OAuthEntryPoint;
use HWI\Bundle\OAuthBundle\Security\Http\Firewall\AbstractRefreshAccessTokenListener;
use HWI\Bundle\OAuthBundle\Security\Http\Firewall\OAuthListener;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('hwi_oauth.authentication.listener.oauth', OAuthListener::class)
        ->abstract()
        ->parent('security.authentication.listener.abstract');

    $services->set('hwi_oauth.authentication.provider.oauth', OAuthProvider::class);

    $services->set('hwi_oauth.authentication.entry_point.oauth', OAuthEntryPoint::class)
        ->args([
            service('http_kernel'),
            service('security.http_utils'),
        ]);

    $services->set('hwi_oauth.user.provider', OAuthUserProvider::class);

    $services->set('hwi_oauth.user.provider.entity', EntityUserProvider::class)
        ->args([service('doctrine')]);

    $services->set('hwi_oauth.context_listener.abstract_token_refresher', AbstractRefreshAccessTokenListener::class)
        ->abstract()
        ->arg(0, abstract_arg('OAuthAuthenticator or AuthenticationProviderInterface'))
        ->call('setTokenStorage', [service('security.token_storage')]);

    // Session storage
    $services->set('hwi_oauth.storage.session', SessionStorage::class)
        ->args([service('request_stack')]);

    $services->set('hwi_oauth.security.oauth_utils', OAuthUtils::class)
        ->args([
            service('security.http_utils'),
            service('security.authorization_checker'),
            service('security.firewall.map'),
            '%hwi_oauth.connect%',
            '%hwi_oauth.grant_rule%',
        ]);
};
