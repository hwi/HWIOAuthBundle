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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for the extension.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * Array of supported resource owners, indentation is intentional to easily notice
     * which resource is of which type.
     *
     * @var array
     */
    private static $resourceOwners = [
        'oauth2' => [
            'amazon',
            'apple',
            'asana',
            'auth0',
            'azure',
            'bitbucket2',
            'bitly',
            'box',
            'bufferapp',
            'clever',
            'dailymotion',
            'deviantart',
            'deezer',
            'disqus',
            'eve_online',
            'eventbrite',
            'facebook',
            'fiware',
            'foursquare',
            'genius',
            'github',
            'gitlab',
            'google',
            'youtube',
            'hubic',
            'instagram',
            'jawbone',
            'keycloak',
            'linkedin',
            'mailru',
            'odnoklassniki',
            'office365',
            'paypal',
            'qq',
            'reddit',
            'runkeeper',
            'salesforce',
            'sensio_connect',
            'sina_weibo',
            'slack',
            'spotify',
            'soundcloud',
            'stack_exchange',
            'strava',
            'toshl',
            'trakt',
            'twitch',
            'vkontakte',
            'windows_live',
            'wordpress',
            'wunderlist',
            'yandex',
            '37signals',
            'itembase',
        ],
        'oauth1' => [
            'bitbucket',
            'discogs',
            'dropbox',
            'flickr',
            'jira',
            'stereomood',
            'trello',
            'twitter',
            'xing',
            'yahoo',
        ],
    ];

    /**
     * Return the type (OAuth1 or OAuth2) of given resource owner.
     *
     * @param string $resourceOwner
     *
     * @return string
     */
    public static function getResourceOwnerType($resourceOwner)
    {
        $resourceOwner = strtolower($resourceOwner);
        if ('oauth1' === $resourceOwner || 'oauth2' === $resourceOwner) {
            return $resourceOwner;
        }

        if (\in_array($resourceOwner, static::$resourceOwners['oauth1'], true)) {
            return 'oauth1';
        }

        return 'oauth2';
    }

    /**
     * Checks that given resource owner is supported by this bundle.
     *
     * @param string $resourceOwner
     *
     * @return bool
     */
    public static function isResourceOwnerSupported($resourceOwner)
    {
        $resourceOwner = strtolower($resourceOwner);
        if ('oauth1' === $resourceOwner || 'oauth2' === $resourceOwner) {
            return true;
        }

        if (\in_array($resourceOwner, static::$resourceOwners['oauth1'], true)) {
            return true;
        }

        return \in_array($resourceOwner, static::$resourceOwners['oauth2'], true);
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder $builder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('hwi_oauth');

        if (method_exists($builder, 'getRootNode')) {
            $rootNode = $builder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $builder->root('hwi_oauth');
        }

        $rootNode
            ->fixXmlConfig('firewall_name')
            ->children()
                ->arrayNode('firewall_names')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
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

        $this->addHttpClientConfiguration($rootNode);
        $this->addConnectConfiguration($rootNode);
        $this->addFosubConfiguration($rootNode);
        $this->addResourceOwnersConfiguration($rootNode);

        return $builder;
    }

    private function addResourceOwnersConfiguration(ArrayNodeDefinition $node)
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
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('authorization_url')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('request_token_url')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('revoke_token_url')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('infos_url')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('client_id')->cannotBeEmpty()->end()
                            ->scalarNode('client_secret')->cannotBeEmpty()->end()
                            ->scalarNode('realm')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('scope')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('user_response_class')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('service')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('class')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('type')
                                ->validate()
                                    ->ifTrue(function ($type) {
                                        return !self::isResourceOwnerSupported($type);
                                    })
                                    ->thenInvalid('Unknown resource owner type "%s".')
                                ->end()
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
                                    ->thenUnset()
                                ->end()
                            ->end()
                            ->scalarNode('use_authorization_to_get_token')
                                ->validate()
                                    ->ifTrue(function ($v) {
                                        return empty($v);
                                    })
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
                                foreach (['type', 'client_id', 'client_secret'] as $child) {
                                    if (!isset($c[$child])) {
                                        return true;
                                    }
                                }

                                return false;
                            })
                            ->thenInvalid("You should set at least the 'type', 'client_id' and the 'client_secret' of a resource owner.")
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
                                if (!isset($c['class'])) {
                                    return false;
                                }

                                return 'oauth2' !== $c['type'] && 'oauth1' !== $c['type'];
                            })
                            ->thenInvalid("If you're setting a 'class', you must provide a 'oauth1' or 'oauth2' type")
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addHttpClientConfiguration(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('http')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('client')->defaultValue('httplug.client.default')->end()
                        ->scalarNode('message_factory')->defaultValue('httplug.message_factory.default')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addConnectConfiguration(ArrayNodeDefinition $node)
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

    private function addFosubConfiguration(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('fosub')
                    ->children()
                        ->scalarNode('username_iterations')->defaultValue(5)->cannotBeEmpty()->end()
                        ->arrayNode('properties')
                            ->isRequired()
                            ->useAttributeAsKey('name')
                                ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
