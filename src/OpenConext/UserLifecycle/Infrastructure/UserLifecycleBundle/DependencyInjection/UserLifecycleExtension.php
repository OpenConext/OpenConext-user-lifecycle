<?php

/**
 * Copyright 2018 SURFnet bv
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


use GuzzleHttp\Client;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Client\DeprovisionClient;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class UserLifecycleExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(
            __DIR__.'/../Resources/config'
        ));
        $loader->load('services.yml');

        $clientConfig = $config[0]['clients'];
        $this->loadDeprovisionClients($clientConfig, $container);
    }

    /**
     * Loads the DeprovisionClients into the DI container.
     * Each client is tagged so it can later be added to a
     * collection class
     *
     * @param array $clients
     * @param ContainerBuilder $container
     */
    private function loadDeprovisionClients(array $clients, ContainerBuilder $container)
    {
        foreach ($clients as $clientName => $clientConfiguration) {
            $definition = new Definition(DeprovisionClient::class);
            $definition->addTag('open_conext.user_lifecycle.deprovision_client');

            $guzzleDefinition = $this->buildGuzzleClientDefinition($clientConfiguration, $clientName, $container);

            $definition->setArgument(0, $guzzleDefinition);

            $container->setDefinition(
                sprintf("open_conext.user_lifecycle.deprovision_client.%s", $clientName),
                $definition
            );
        }
    }

    /**
     * Creates the Guzzle http client definition for a
     * given deprovision client.
     *
     * @param $config
     * @param $clientName
     * @param ContainerBuilder $container
     */
    private function buildGuzzleClientDefinition($config, $clientName, ContainerBuilder $container)
    {
        $definition = new Definition(Client::class);

        $definition->setArgument(0, [
            'base_uri' => $config['url'],
            'verify' => isset($config['verify_ssl']) ? $config['verify_ssl'] : true,
            'auth' => [
                $config['username'],
                $config['password'],
                'basic'
            ],
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $container->setDefinition(
            sprintf("open_conext.user_lifecycle.guzzle_client.%s", $clientName),
            $definition
        );

    }
}
