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

use HWI\Bundle\OAuthBundle\Util\DomainWhitelist;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('hwi_oauth.util.domain_whitelist', DomainWhitelist::class)
        ->args(['%hwi_oauth.target_path_domains_whitelist%']);

    $services->set('hwi_oauth.http_client', HttpClientInterface::class)
        ->factory([HttpClient::class, 'create'])
        ->tag('http_client.client');
};
