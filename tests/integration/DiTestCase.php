<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace OpenConext\UserLifecycle\Tests\Integration;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

abstract class DiTestCase extends AbstractExtensionTestCase
{
    /**
     * Overridden the load method as the $configurationValues
     * where not passed into the load method in the correct
     * order.
     *
     * @param array $configurationValues
     */
    protected function load(array $configurationValues = array())
    {
        $configs = [$configurationValues, $this->getMinimalConfiguration()];

        foreach ($this->container->getExtensions() as $extension) {
            $extension->load($configs, $this->container);
        }
    }
}
