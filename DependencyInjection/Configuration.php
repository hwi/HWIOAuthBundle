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
                        ->scalarNode('client_id')
                            ->cannotBeEmpty()
                            ->isRequired()
                        ->end()
                        ->scalarNode('client_secret')
                            ->cannotBeEmpty()
                            ->isRequired()
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
                            ->isRequired()
                        ->end()
                        ->scalarNode('user_response_class')
                            ->validate()
                                ->ifTrue(function($v) {
                                    return empty($v);
                                })
                                ->thenUnset()
                            ->end()
                        ->end()
                        ->scalarNode('type')
                            ->defaultValue('generic')
                            ->validate()
                                ->ifNotInArray(array('facebook', 'generic', 'github', 'google'))
                                ->thenInvalid('Unknow resource owner type %s.')
                            ->end()
                        ->end()
                        ->scalarNode('username_path')
                            ->validate()
                                ->ifTrue(function($v) {
                                    return empty($v);
                                })
                                ->thenUnset()
                            ->end()
                        ->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function($c) {
                            if ('generic' === $c['type']) {
                                $children = array('authorization_url', 'access_token_url', 'infos_url');
                                foreach ($children as $child) {
                                    if (!isset($c[$child])) {
                                        return true;
                                    }
                                }

                                // one of the two should be set
                                return !isset($c['username_path']) && !isset($c['user_response_class']);
                            }

                            return false;
                        })
                        ->thenInvalid("All parameters are mandatory for type 'generic'. Check if you're missing one of: access_token_url, authorization_url, infos_url or username_path or user_response_class.")
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
