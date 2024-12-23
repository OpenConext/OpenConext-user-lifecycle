<?php

declare(strict_types = 1);

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

use GuzzleHttp\Client;
use OpenConext\UserLifecycle\Application\Client\InformationResponseFactory;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Client\DeprovisionClient;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Webmozart\Assert\Assert;

class UserLifecycleExtension extends Extension
{
    public function load(
        array $configs,
        ContainerBuilder $container,
    ): void {

        foreach ($configs as $configSet) {
            // Do not evaluate empty configurations
            if (empty($configSet)) {
                continue;
            }

            if (isset($configSet['clients'])) {
                Assert::notEmpty($configSet['clients'], 'Configure at least one deprovision API in parameters.yml');

                $clientConfig = $configSet['clients'];
                $this->loadDeprovisionClients($clientConfig, $container);
            }
        }
    }

    /**
     * Loads the DeprovisionClients into the DI container.
     * Each client is tagged so it can later be added to a
     * collection class
     */
    private function loadDeprovisionClients(
        array $clients,
        ContainerBuilder $container,
    ): void {
        foreach ($clients as $clientName => $clientConfiguration) {
            $definition = new Definition(DeprovisionClient::class);
            $definition->addTag('open_conext.user_lifecycle.deprovision_client');
            $guzzleDefinition = $this->buildGuzzleClientDefinition($clientConfiguration, $clientName, $container);

            $factoryDefinition = new Definition(InformationResponseFactory::class);

            // Set the guzzle client on the DeprovisionClient
            $definition->setArgument(0, $guzzleDefinition);

            // Set the information response factory on the DeprovisionClient
            $definition->setArgument(1, $factoryDefinition);

            // Set the client name on the DeprovisionClient
            $definition->setArgument(2, $clientName);

            $container->setDefinition(
                sprintf("open_conext.user_lifecycle.deprovision_client.%s", $clientName),
                $definition,
            );
        }
    }

    /**
     * Creates the Guzzle http client definition for a
     * given deprovision client.
     *
     * @param $config
     * @param $clientName
     *
     * @return Definition
     */
    private function buildGuzzleClientDefinition(
        $config,
        int|string $clientName,
        ContainerBuilder $container,
    ): Definition {
        $definition = new Definition(Client::class);

        // Perform input validation on the config
        $requiredKeys = ['url', 'username', 'password'];
        $message = 'Expected the key "%s" to exist in deprovision API client configuration "%s".';
        foreach ($requiredKeys as $key) {
            Assert::keyExists(
                $config,
                $key,
                sprintf($message, $key, $clientName),
            );
        }

        // Configure Guzzle
        $definition->setArgument(0, [
            'base_uri' => $config['url'],
            'verify' => $config['verify_ssl'] ?? true,
            'auth' => [
                $config['username'],
                $config['password'],
                'basic'
            ],
            'headers' => [
                'Accept' => 'application/json'
            ],
            'handler' => new Reference('open_conext.user_lifecycle.deprovision_client.guzzle_stack'),
        ]);

        $container->setDefinition(
            sprintf("open_conext.user_lifecycle.guzzle_client.%s", $clientName),
            $definition,
        );

        return $definition;
    }
}
