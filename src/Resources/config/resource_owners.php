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

use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMapLocator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('hwi_oauth.abstract_resource_ownermap', ResourceOwnerMap::class)
        ->abstract()
        ->arg('$httpUtils', service('security.http_utils'))
        ->arg('$possibleResourceOwners', '%hwi_oauth.resource_owners%')
        ->arg('$resourceOwners', []);

    $services->set('hwi_oauth.resource_ownermap_locator', ResourceOwnerMapLocator::class);
};
