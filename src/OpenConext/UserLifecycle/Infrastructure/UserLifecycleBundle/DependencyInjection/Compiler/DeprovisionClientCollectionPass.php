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

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\DependencyInjection\Compiler;

use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientCollectionInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The deprovision client collection is registered with the services tagged with
 * 'open_conext.user_lifecycle.deprovision_client'.
 */
class DeprovisionClientCollectionPass implements CompilerPassInterface
{
    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable) $tags is never used in the foreach
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(DeprovisionClientCollectionInterface::class)) {
            return;
        }

        $definition = $container->findDefinition(DeprovisionClientCollectionInterface::class);

        // find all service IDs with the open_conext.user_lifecycle.deprovision_client tag
        $taggedServices = $container->findTaggedServiceIds('open_conext.user_lifecycle.deprovision_client');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addClient', array(new Reference($id)));
        }
    }
}
