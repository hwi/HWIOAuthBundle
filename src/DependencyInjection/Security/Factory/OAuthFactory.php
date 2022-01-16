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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Provider\OAuthProvider;
use HWI\Bundle\OAuthBundle\Security\Http\Firewall\RefreshAccessTokenListenerOld;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 * @author Alexander <iam.asm89@gmail.com>
 *
 * @deprecated
 */
final class OAuthFactory extends AbstractFactory
{
    /**
     * @param ArrayNodeDefinition $node
     */
    public function addConfiguration(NodeDefinition $node): void
    {
        parent::addConfiguration($node);

        $builder = $node->children();
        $builder
            ->scalarNode('login_path')->cannotBeEmpty()->isRequired()->end()
        ;

        $this->addOAuthProviderConfiguration($node);
        $this->addResourceOwnersConfiguration($node);
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return 'oauth';
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition(): string
    {
        return 'http';
    }

    public function getPriority(): int
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     */
    public function createAuthenticator(
        ContainerBuilder $container,
        string $firewallName,
        array $config,
        string $userProviderId
    ): string {
        throw new \RuntimeException('Deprecated "OAuthFactory" cannot create new Symfony Authenticator!');
    }

    /**
     * {@inheritdoc}
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId): string
    {
        $providerId = 'hwi_oauth.authentication.provider.oauth.'.$id;

        $this->createResourceOwnerMap($container, $id, $config);

        $container
            ->register('hwi_oauth.authentication.provider.oauth', OAuthProvider::class);

        $container
            ->setDefinition($providerId, new ChildDefinition('hwi_oauth.authentication.provider.oauth'))
            ->addArgument($this->createOAuthAwareUserProvider($container, $id, $config['oauth_user_provider']))
            ->addArgument($this->createResourceOwnerMapReference($id))
            ->addArgument(new Reference('hwi_oauth.user_checker'))
            ->addArgument(new Reference('security.token_storage'))
        ;

        $this->createRefreshTokenListeners($container, $providerId, $id);

        return $providerId;
    }

    protected function createOAuthAwareUserProvider(ContainerBuilder $container, $id, $config): Reference
    {
        $serviceId = 'hwi_oauth.user.provider.entity.'.$id;

        // todo: move this to factories?
        switch (key($config)) {
            case 'oauth':
                $container
                    ->setDefinition($serviceId, new ChildDefinition('hwi_oauth.user.provider'))
                ;
                break;
            case 'orm':
                $container
                    ->setDefinition($serviceId, new ChildDefinition('hwi_oauth.user.provider.entity'))
                    ->addArgument($config['orm']['class'])
                    ->addArgument($config['orm']['properties'])
                    ->addArgument($config['orm']['manager_name'])
                ;
                break;
            case 'service':
                $container
                    ->setAlias($serviceId, $config['service']);
                break;
        }

        return new Reference($serviceId);
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntryPoint($container, $id, $config, $defaultEntryPointId): ?string
    {
        $entryPointId = 'hwi_oauth.authentication.entry_point.oauth.'.$id;

        $container
            ->setDefinition($entryPointId, new ChildDefinition('hwi_oauth.authentication.entry_point.oauth'))
            ->addArgument($config['login_path'])
            ->addArgument($config['use_forward'])
        ;

        return $entryPointId;
    }

    /**
     * {@inheritdoc}
     */
    protected function createListener($container, $id, $config, $userProvider): string
    {
        $listenerId = parent::createListener($container, $id, $config, $userProvider);

        $checkPaths = $config['resource_owners'];

        $container
            ->getDefinition($listenerId)
            ->addMethodCall('setResourceOwnerMap', [$this->createResourceOwnerMapReference($id)])
            ->addMethodCall('setCheckPaths', [$checkPaths])
        ;

        return $listenerId;
    }

    protected function createRefreshTokenListeners(ContainerBuilder $container, string $providerId, string $id): void
    {
        $listenerId = 'hwi_oauth.context_listener.token_refresher.'.$id;

        $listenerDef = $container->setDefinition($listenerId, new ChildDefinition('hwi_oauth.context_listener.abstract_token_refresher'));

        $listenerDef
            ->addMethodCall('setResourceOwnerMap', [$this->createResourceOwnerMapReference($id)])
            ->setClass(RefreshAccessTokenListenerOld::class)
            ->replaceArgument(0, new Reference($providerId));
    }

    /**
     * {@inheritdoc}
     */
    protected function getListenerId(): string
    {
        return 'hwi_oauth.authentication.listener.oauth';
    }

    /**
     * Gets a reference to the resource owner map.
     */
    private function createResourceOwnerMapReference(string $firewallName): Reference
    {
        return new Reference('hwi_oauth.resource_ownermap.'.$firewallName);
    }

    /**
     * Creates a resource owner map for the given configuration.
     *
     * @param ContainerBuilder $container Container to build for
     * @param array            $config    Configuration
     */
    private function createResourceOwnerMap(ContainerBuilder $container, string $firewallName, array $config): void
    {
        $resourceOwnersMap = [];
        foreach ($config['resource_owners'] as $name => $checkPath) {
            $resourceOwnersMap[$name] = $checkPath;
        }
        $container->setParameter('hwi_oauth.resource_ownermap.configured.'.$firewallName, $resourceOwnersMap);

        $container
            ->setDefinition(
                $this->createResourceOwnerMapReference($firewallName),
                new ChildDefinition('hwi_oauth.abstract_resource_ownermap')
            )
            ->replaceArgument('$resourceOwners', new Parameter('hwi_oauth.resource_ownermap.configured.'.$firewallName))
            ->setPublic(true)
        ;
    }

    private function addOAuthProviderConfiguration(ArrayNodeDefinition $node): void
    {
        $builder = $node->children();
        $builder
            ->arrayNode('oauth_user_provider')
                ->isRequired()
                ->children()
                    ->arrayNode('orm')
                        ->children()
                            ->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('manager_name')->defaultNull()->end()
                            ->arrayNode('properties')
                                ->isRequired()
                                ->useAttributeAsKey('name')
                                    ->prototype('scalar')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('service')->cannotBeEmpty()->end()
                    ->scalarNode('oauth')->end()
                ->end()
                ->validate()
                    ->ifTrue(function ($c) {
                        return 1 !== \count($c) || !\in_array(key($c), ['oauth', 'orm', 'service'], true);
                    })
                    ->thenInvalid("You should configure (only) one of: 'oauth', 'orm', 'service'.")
                ->end()
            ->end()
        ;
    }

    private function addResourceOwnersConfiguration(ArrayNodeDefinition $node): void
    {
        $builder = $node->children();
        $builder
            ->arrayNode('resource_owners')
                ->isRequired()
                ->useAttributeAsKey('name')
                    ->prototype('scalar')
                ->end()
                ->validate()
                    ->ifTrue(function ($c) {
                        $checkPaths = [];
                        foreach ($c as $checkPath) {
                            if (\in_array($checkPath, $checkPaths, true)) {
                                return true;
                            }

                            $checkPaths[] = $checkPath;
                        }

                        return false;
                    })
                    ->thenInvalid('Each resource owner should have a unique "check_path".')
                ->end()
            ->end()
        ;
    }
}
