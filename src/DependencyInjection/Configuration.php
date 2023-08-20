<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\DependencyInjection;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\Config\Definition\BaseNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Finder\Finder;

/**
 * Configuration for the extension.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * type => ResourceOwner mapping for hwi_oauth.resource_owner.*.class parameters.
     *
     * @var array<string, class-string<GenericOAuth1ResourceOwner|GenericOAuth2ResourceOwner|ResourceOwnerInterface>>
     */
    private static array $resourceOwnerTypesClassMap = [];

    /**
     * Array of supported resource owners.
     *
     * @var array<string, string>
     */
    private static array $resourceOwnerTypes = [];

    public function __construct()
    {
        if ([] === self::$resourceOwnerTypes) {
            self::loadResourceOwners();
        }
    }

    public static function getResourceOwnerTypesClassMap(): array
    {
        return self::$resourceOwnerTypesClassMap;
    }

    /**
     * Return the type (oauth1 or oauth2) of given resource owner.
     */
    public static function getResourceOwnerType(string $resourceOwner): ?string
    {
        $resourceOwner = strtolower($resourceOwner);

        return self::$resourceOwnerTypes[$resourceOwner] ?? null;
    }

    /**
     * Checks that given resource owner is supported by this bundle.
     */
    public static function isResourceOwnerSupported(string $resourceOwner): bool
    {
        return isset(self::$resourceOwnerTypes[strtolower($resourceOwner)]);
    }

    public static function registerResourceOwner(string $resourceOwnerClass): void
    {
        $reflection = new \ReflectionClass($resourceOwnerClass);
        if (!$reflection->implementsInterface(ResourceOwnerInterface::class)) {
            throw new \LogicException('Resource owner class should implement "ResourceOwnerInterface", or extended class "GenericOAuth1ResourceOwner"/"GenericOAuth2ResourceOwner".');
        }

        $type = \defined("$resourceOwnerClass::TYPE") ? $resourceOwnerClass::TYPE : null;
        if (null === $type) {
            if (preg_match('~(?P<resource_owner>[^\\\\]+)ResourceOwner$~', $resourceOwnerClass, $match)) {
                $type = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $match['resource_owner']));
            } else {
                throw new \LogicException(sprintf('Resource owner class either should have "TYPE" const defined or end with "ResourceOwner" so that type can be calculated by converting its class name without suffix to "snake_case". Given class name is "%s"', $resourceOwnerClass));
            }
        }

        $oAuth = 'unknown';
        if ($reflection->isSubclassOf(GenericOAuth2ResourceOwner::class)) {
            $oAuth = 'oauth2';
        } elseif ($reflection->isSubclassOf(GenericOAuth1ResourceOwner::class)) {
            $oAuth = 'oauth1';
        }

        self::$resourceOwnerTypes[$type] = $oAuth;
        self::$resourceOwnerTypesClassMap[$type] = $resourceOwnerClass;
    }

    /**
     * Generates the configuration tree builder.
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('hwi_oauth');

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $builder->getRootNode();

        $rootNode
            ->fixXmlConfig('firewall_name')
            ->children()
                ->arrayNode('firewall_names')
                    ->setDeprecated(...$this->getDeprecationParams())
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('target_path_parameter')->defaultNull()->end()
                ->arrayNode('target_path_domains_whitelist')
                    ->defaultValue([])
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('use_referer')->defaultFalse()->end()
                ->booleanNode('failed_use_referer')->defaultFalse()->end()
                ->scalarNode('failed_auth_path')->defaultValue('hwi_oauth_connect')->end()
                ->scalarNode('grant_rule')
                    ->defaultValue('IS_AUTHENTICATED_REMEMBERED')
                    ->validate()
                        ->ifTrue(function ($role) {
                            return !('IS_AUTHENTICATED_REMEMBERED' === $role || 'IS_AUTHENTICATED_FULLY' === $role);
                        })
                        ->thenInvalid('Unknown grant role set "%s".')
                    ->end()
                ->end()
            ->end()
        ;

        $this->addConnectConfiguration($rootNode);
        $this->addResourceOwnersConfiguration($rootNode);

        return $builder;
    }

    private function addResourceOwnersConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->fixXmlConfig('resource_owner')
            ->children()
                ->arrayNode('resource_owners')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->ignoreExtraKeys()
                        ->children()
                            ->scalarNode('base_url')->end()
                            ->scalarNode('access_token_url')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('authorization_url')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('request_token_url')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('revoke_token_url')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('infos_url')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('client_id')->cannotBeEmpty()->end()
                            ->scalarNode('client_secret')->cannotBeEmpty()->end()
                            ->scalarNode('realm')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('scope')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('user_response_class')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('service')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('class')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('type')
                                // will be validated in ResourceOwnerCompilerPass, other apps can register own resource
                                // owner maps later with tag hwi_oauth.resource_owner
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('use_authorization_to_get_token')
                                ->validate()
                                    ->ifEmpty()
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->arrayNode('paths')
                                ->useAttributeAsKey('name')
                                ->prototype('variable')
                                    ->validate()
                                        ->ifTrue(function ($v) {
                                            if (null === $v) {
                                                return true;
                                            }

                                            if (\is_array($v)) {
                                                return 0 === \count($v);
                                            }

                                            if (\is_string($v)) {
                                                return empty($v);
                                            }

                                            return !is_numeric($v);
                                        })
                                        ->thenInvalid('Path can be only string or array type.')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('options')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->validate()
                            ->ifTrue(function ($c) {
                                // skip if this contains a service
                                if (isset($c['service'])) {
                                    return false;
                                }

                                // for each type at least these have to be set
                                foreach (['client_id', 'client_secret'] as $child) {
                                    if (!isset($c[$child])) {
                                        return true;
                                    }
                                }

                                if (!isset($c['type']) && !isset($c['class'])) {
                                    return true;
                                }

                                return false;
                            })
                            ->thenInvalid("You should set at least the 'type' or 'class' with 'client_id' and the 'client_secret' of a resource owner.")
                        ->end()
                        ->validate()
                            ->ifTrue(function ($c) {
                                return isset($c['type'], $c['class']);
                            })
                            ->then(function ($c) {
                                trigger_deprecation('hwi/oauth-bundle', '2.0', 'No need to set both "type" and "class" for resource owner.');

                                return $c;
                            })
                        ->end()
                        ->validate()
                            ->ifTrue(function ($c) {
                                // Skip if this contains a service or a class
                                if (isset($c['service']) || isset($c['class'])) {
                                    return false;
                                }

                                // Only validate the 'oauth2' and 'oauth1' type
                                if ('oauth2' !== $c['type'] && 'oauth1' !== $c['type']) {
                                    return false;
                                }

                                $children = ['authorization_url', 'access_token_url', 'request_token_url', 'infos_url'];
                                foreach ($children as $child) {
                                    // This option exists only for OAuth1.0a
                                    if ('request_token_url' === $child && 'oauth2' === $c['type']) {
                                        continue;
                                    }

                                    if (!isset($c[$child])) {
                                        return true;
                                    }
                                }

                                return false;
                            })
                            ->thenInvalid("All parameters are mandatory for types 'oauth2' and 'oauth1'. Check if you're missing one of: 'access_token_url', 'authorization_url', 'infos_url' and 'request_token_url' for 'oauth1'.")
                        ->end()
                        ->validate()
                            ->ifTrue(function ($c) {
                                // skip if this contains a service
                                if (isset($c['service']) || isset($c['class'])) {
                                    return false;
                                }

                                // Only validate the 'oauth2' and 'oauth1' type
                                if ('oauth2' !== $c['type'] && 'oauth1' !== $c['type']) {
                                    return false;
                                }

                                // one of this two options must be set
                                if (0 === \count($c['paths'])) {
                                    return !isset($c['user_response_class']);
                                }

                                foreach (['identifier', 'nickname', 'realname'] as $child) {
                                    if (!isset($c['paths'][$child])) {
                                        return true;
                                    }
                                }

                                return false;
                            })
                            ->thenInvalid("At least the 'identifier', 'nickname' and 'realname' paths should be configured for 'oauth2' and 'oauth1' types.")
                        ->end()
                        ->validate()
                            ->ifTrue(function ($c) {
                                if (isset($c['service'])) {
                                    // ignore paths & options if none were set
                                    return 0 !== \count($c['paths']) || 0 !== \count($c['options']) || 3 < \count($c);
                                }

                                return false;
                            })
                            ->thenInvalid("If you're setting a 'service', no other arguments should be set.")
                        ->end()
                        ->validate()
                            ->ifTrue(function ($c) {
                                return isset($c['class']);
                            })
                            ->then(function ($c) {
                                self::registerResourceOwner($c['class']);

                                return $c;
                            })
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addConnectConfiguration(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('connect')
                    ->children()
                        ->booleanNode('confirmation')->defaultTrue()->end()
                        ->scalarNode('account_connector')->cannotBeEmpty()->end()
                        ->scalarNode('registration_form_handler')->cannotBeEmpty()->end()
                        ->scalarNode('registration_form')->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private static function loadResourceOwners(): void
    {
        $files = (new Finder())
            ->in(__DIR__.'/../OAuth/ResourceOwner')
            ->name('~^(.+)ResourceOwner\.php$~')
            ->files();

        foreach ($files as $f) {
            if (!str_contains($f->getFilename(), 'ResourceOwner')) {
                continue;
            }

            // Skip known abstract classes
            if (\in_array($f->getFilename(), ['AbstractResourceOwner.php', 'GenericOAuth1ResourceOwner.php', 'GenericOAuth2ResourceOwner.php'], true)) {
                continue;
            }

            self::registerResourceOwner('HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\\'.str_replace('.php', '', $f->getFilename()));
        }
    }

    /**
     * Returns the correct deprecation params as an array for setDeprecated().
     *
     * symfony/config v5.1 introduces a deprecation notice when calling
     * setDeprecated() with less than 3 args and the getDeprecation() method was
     * introduced at the same time. By checking if getDeprecation() exists,
     * we can determine the correct param count to use when calling setDeprecated().
     *
     * @return string[]
     */
    private function getDeprecationParams(): array
    {
        if (method_exists(BaseNode::class, 'getDeprecation')) {
            return [
                'hwi/oauth-bundle',
                '2.0',
                'option "%path%.%node%" is deprecated. Firewall names are collected automatically.',
            ];
        }

        return ['Since hwi/oauth-bundle 2.0: option "hwi_oauth.firewall_names" is deprecated. Firewall names are collected automatically.'];
    }
}
