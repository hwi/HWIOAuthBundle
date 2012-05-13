<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for the extension
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder $builder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $rootNode = $builder->root('hwi_oauth');
        $rootNode
            ->fixXmlConfig('resource_owner')
            ->children()
            ->scalarNode('firewall_name')->defaultValue(false)->end()
            ->arrayNode('fosub')
                ->children()
                    ->scalarNode('username_iterations')
                        ->defaultValue(5)
                        ->cannotBeEmpty()
                    ->end()
                    ->arrayNode('properties')
                        ->isRequired()
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('connect')
                ->children()
                    ->scalarNode('account_connector')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('registration_form_handler')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('registration_form')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('resource_owners')
                ->isRequired()
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('access_token_url')
                            ->validate()
                                ->ifTrue(function($v) {
                                    return empty($v);
                                })
                                ->thenUnset()
                            ->end()
                        ->end()
                        ->scalarNode('authorization_url')
                            ->validate()
                                ->ifTrue(function($v) {
                                    return empty($v);
                                })
                                ->thenUnset()
                            ->end()
                        ->end()
                        ->scalarNode('access_token_encode')
                            ->validate()
                                ->ifTrue(function($v) {
                                    return empty($v);
                                })
                                ->thenUnset()
                            ->end()
                        ->end()                        
                        ->scalarNode('client_id')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('client_secret')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('infos_url')
                            ->validate()
                                ->ifTrue(function($v) {
                                    return empty($v);
                                })
                                ->thenUnset()
                            ->end()
                        ->end()
                        ->scalarNode('scope')
                        ->end()
                        ->scalarNode('user_response_class')
                            ->validate()
                                ->ifTrue(function($v) {
                                    return empty($v);
                                })
                                ->thenUnset()
                            ->end()
                        ->end()
                        ->scalarNode('service')
                            ->validate()
                                ->ifTrue(function($v) {
                                    return empty($v);
                                })
                                ->thenUnset()
                            ->end()
                        ->end()
                        ->scalarNode('type')
                            ->validate()
                                ->ifNotInArray(array('facebook', 'generic', 'github', 'google', 'windows_live', 'viadeo'))
                                ->thenInvalid('Unknown resource owner type %s.')
                            ->end()
                            ->validate()
                                ->ifTrue(function($v) {
                                    return empty($v);
                                })
                                ->thenUnset()
                            ->end()
                        ->end()
                        ->arrayNode('paths')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function($c) {
                            // skip if this contains a service
                            if (isset($c['service'])) {
                                return false;
                            }

                            // for each type at least these have to be set
                            $children = array('type', 'client_id', 'client_secret');
                            foreach ($children as $child) {
                                if (!isset($c[$child])) {
                                    return true;
                                }
                            }

                            return false;
                        })
                        ->thenInvalid('You should set at least the type, client_id and the client_secret of a resource owner.')
                    ->end()
                    ->validate()
                        ->ifTrue(function($c) {
                            // skip if this contains a service
                            if (isset($c['service'])) {
                                return false;
                            }

                            // Only validate the 'generic' type
                            if ('generic' !== $c['type']) {
                                return false;
                            }

                            $children = array('authorization_url', 'access_token_url', 'infos_url');
                            foreach ($children as $child) {
                                if (!isset($c[$child])) {
                                    return true;
                                }
                            }

                            // one of the two should be set
                            return !isset($c['paths']) && !isset($c['user_response_class']);
                        })
                        ->thenInvalid("All parameters are mandatory for type 'generic'. Check if you're missing one of: access_token_url, authorization_url, infos_url or paths or user_response_class.")
                    ->end()
                    ->validate()
                        ->ifTrue(function($c) {
                            // skip if this contains a service
                            if (isset($c['service'])) {
                                return false;
                            }

                            // Only validate the 'generic' type
                            if ('generic' !== $c['type']) {
                                return false;
                            }

                            $children = array('username', 'displayname');
                            foreach ($children as $child) {
                                if (!isset($c['paths'][$child])) {
                                    return true;
                                }
                            }

                            // one of the two should be set
                            return !isset($c['paths']) && !isset($c['user_response_class']);
                        })
                        ->thenInvalid("At least 'username' and 'displayname' paths should be configured for the generic type.")
                    ->end()
                    ->validate()
                        ->ifTrue(function($c) {
                            return isset($c['service']) && 1 !== count($c);
                        })
                        ->thenInvalid("If you're setting a service, no other arguments should be set.")
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
