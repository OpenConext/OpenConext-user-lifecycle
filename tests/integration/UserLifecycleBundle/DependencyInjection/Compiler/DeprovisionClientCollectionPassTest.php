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

namespace OpenConext\UserLifecycle\Tests\Integration\UserLifecycleBundle\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\DependencyInjection\Compiler\DeprovisionClientCollectionPass; // phpcs:ignore
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DeprovisionClientCollectionPassTest extends AbstractCompilerPassTestCase
{

    public function test_clients_are_registered_on_collection()
    {
        $deprovisionCollection = new Definition();
        $this->setDefinition('open_conext.user_lifecycle.deprovision_client_collection', $deprovisionCollection);

        // Define and tag the clients
        $deprovisionClient = new Definition();
        $deprovisionClient->addTag('open_conext.user_lifecycle.deprovision_client');
        $this->setDefinition('open_conext.user_lifecycle.deprovision_client.my_service_name', $deprovisionClient);

        $anotherDeprovisionClient = new Definition();
        $anotherDeprovisionClient->addTag('open_conext.user_lifecycle.deprovision_client');
        $this->setDefinition('open_conext.user_lifecycle.deprovision_client.my_second_name', $anotherDeprovisionClient);

        // Run the compiler pass
        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'open_conext.user_lifecycle.deprovision_client_collection',
            'addClient',
            [
                new Reference('open_conext.user_lifecycle.deprovision_client.my_service_name'),
            ]
        );

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'open_conext.user_lifecycle.deprovision_client_collection',
            'addClient',
            [
                new Reference('open_conext.user_lifecycle.deprovision_client.my_second_name'),
            ]
        );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new DeprovisionClientCollectionPass());
    }
}
