<?php

/**
 * Copyright 2018 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $treeBuilder
            ->root('user_lifecycle')
                ->children()
                    ->arrayNode('clients')
                        ->info('Configures the deprovision clients.')
                        ->isRequired()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('url')
                                ->isRequired()
                                ->validate()
                                    ->ifTrue(function ($url) {
                                        return !is_string($url) || empty($url);
                                    })
                                    ->thenInvalid("Url must be non-empty string, got '%s'")
                                ->end()
                            ->end()
                            ->scalarNode('username')
                                ->isRequired()
                                ->validate()
                                    ->ifTrue(function ($username) {
                                        return !is_string($username) || empty($username);
                                    })
                                    ->thenInvalid("Username must be non-empty string, got '%s'")
                                ->end()
                            ->end()
                            ->scalarNode('password')
                                ->isRequired()
                                ->validate()
                                    ->ifTrue(function ($password) {
                                        return !is_string($password) || empty($password);
                                    })
                                    ->thenInvalid("Password must be non-empty string, got '%s'")
                                ->end()
                            ->end()
                            ->booleanNode('verify_ssl')
                                ->defaultValue(true)
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('deprovision_api')
                        ->info('Configures the deprovision API.')
                        ->canBeDisabled()
                        ->addDefaultsIfNotSet()
                        ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                            ->validate()
                                ->ifTrue(function ($enabled) {
                                    return is_bool($enabled);
                                })
                                ->thenInvalid("Enabled must be a boolean, got '%s'")
                            ->end()
                        ->end()
                        ->scalarNode('username')
                                ->ifTrue(function ($username) {
                                    return !is_string($username) || empty($username);
                                })
                                ->thenInvalid("Username must be non-empty string, got '%s'")
                            ->end()
                        ->end()
                        ->scalarNode('password')
                            ->validate()
                                ->ifTrue(function ($password) {
                                    return !is_string($password) || empty($password);
                                })
                                ->thenInvalid("Password must be non-empty string, got '%s'")
                            ->end()
                        ->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
