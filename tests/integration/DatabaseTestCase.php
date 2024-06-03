<?php

declare(strict_types = 1);

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

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\UserLifecycle\Domain\Entity\LastLogin;
use OpenConext\UserLifecycle\Tests\Integration\DataFixtures\ORM\DatabaseTestFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The DatabaseTestCase exposes the Kernel and enables
 * the loading of database fixtures. This test case can
 * be used to create repository and console command
 * tests.
 */
abstract class DatabaseTestCase extends KernelTestCase
{
    use MockeryPHPUnitIntegration;


    protected function setUp(): void
    {
        self::$kernel = self::bootKernel();
    }
    
    protected function loadFixtures(): void
    {
        $em = $this->getEntitytManager();

        $loader = new Loader();
        $loader->addFixture(new DatabaseTestFixtures());

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures());
    }

    protected function clearFixtures()
    {
        $em = $this->getEntitytManager();

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute([]);
    }

    protected function getLastLoginRepository()
    {
        return $this->getEntitytManager()->getRepository(LastLogin::class);
    }

    private function getEntitytManager(): ?object
    {
        return self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }
}
