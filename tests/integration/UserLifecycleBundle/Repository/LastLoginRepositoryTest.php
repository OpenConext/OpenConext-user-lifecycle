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

namespace OpenConext\UserLifecycle\Tests\Integration\UserLifecycleBundle\Repository;

use OpenConext\UserLifecycle\Domain\Entity\LastLogin;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Repository\LastLoginRepository;
use OpenConext\UserLifecycle\Tests\Integration\DatabaseTestCase;

class LastLoginRepositoryTest extends DatabaseTestCase
{
    /**
     * @var LastLoginRepository
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->loadFixtures();
        $this->repository = $this->getLastLoginRepository();
    }

    public function test_it_reads_by_user_id()
    {
        $userId = new CollabPersonId('urn:collab:org:surf.nl:james_watson');
        $watson = $this->repository->findLastLoginFor($userId);

        $this->assertInstanceOf(LastLogin::class, $watson);
        $this->assertEquals('urn:collab:org:surf.nl:james_watson', $watson->getCollabPersonId());
        $this->assertEquals('2015-01-01', $watson->getLastLoginDate()->format('Y-m-d'));
    }

    public function test_it_returns_null_if_user_does_not_exist()
    {
        $userId = new CollabPersonId('urn:collab:org:surf.nl:jimmy_jones');
        $result = $this->repository->findLastLoginFor($userId);

        $this->assertNull($result);
    }
}
