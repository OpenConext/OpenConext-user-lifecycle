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

use GuzzleHttp\Client;
use InvalidArgumentException;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Client\DeprovisionClient;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\DependencyInjection\UserLifecycleExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class UserLifecycleExtensionTest extends AbstractExtensionTestCase
{

    /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions(): array
    {
        return [new UserLifecycleExtension()];
    }

    public function test_after_loading_the_correct_services_have_been_set()
    {
        $config = json_decode(
            file_get_contents(__DIR__ . '/Fixtures/user_lifecycle_extension_config_two_services.json'),
            true
        );

        $this->load($config);

        $this->assertContainerBuilderHasService(
            'open_conext.user_lifecycle.deprovision_client.my_service_name',
            DeprovisionClient::class
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'open_conext.user_lifecycle.deprovision_client.my_service_name',
            'open_conext.user_lifecycle.deprovision_client'
        );
        $this->assertContainerBuilderHasService(
            'open_conext.user_lifecycle.deprovision_client.my_second_name',
            DeprovisionClient::class
        );
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'open_conext.user_lifecycle.deprovision_client.my_second_name',
            'open_conext.user_lifecycle.deprovision_client'
        );

        $this->assertContainerBuilderHasService(
            'open_conext.user_lifecycle.guzzle_client.my_service_name',
            Client::class
        );
        $this->assertContainerBuilderHasService(
            'open_conext.user_lifecycle.guzzle_client.my_second_name',
            Client::class
        );
    }

    public function test_rejects_empty_config()
    {
        $config = json_decode(
            file_get_contents(__DIR__ . '/Fixtures/user_lifecycle_extension_no_config.json'),
            true
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Configure at least one deprovision API in parameters.yml');

        $this->load($config);
    }

    public function test_it_rejects_invalid_config()
    {
        $config = json_decode(
            file_get_contents(__DIR__ . '/Fixtures/user_lifecycle_extension_invalid_config.json'),
            true
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Expected the key "password" to exist in deprovision API client configuration "my_service_name"'
        );

        $this->load($config);
    }
}
