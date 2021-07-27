<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\DependencyInjection\Security\Factory;

use HWI\Bundle\OAuthBundle\Security\Http\Authenticator\OAuthAuthenticator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Vadim Borodavko <vadim.borodavko@gmail.com>
 */
final class OAuthAuthenticatorFactory extends OAuthFactory implements AuthenticatorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): string {
        $authenticatorId = 'security.authenticator.oauth.'.$firewallName;

        $this->createResourceOwnerMap($container, $firewallName, $config);

        $container
            ->register($authenticatorId, OAuthAuthenticator::class)
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument(
                $this->createOAuthAwareUserProvider($container, $firewallName, $config['oauth_user_provider'])
            )
            ->addArgument($this->getResourceOwnerMapReference($firewallName))
            ->addArgument($config['resource_owners'])
            ->addArgument(new Reference($this->createAuthenticationSuccessHandler($container, $firewallName, $config)))
            ->addArgument(new Reference($this->createAuthenticationFailureHandler($container, $firewallName, $config)))
        ;

        return $authenticatorId;
    }
}
