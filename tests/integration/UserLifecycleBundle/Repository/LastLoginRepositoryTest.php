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

use DateTime;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Repository\LastLoginRepository;
use OpenConext\UserLifecycle\Tests\Integration\DatabaseTestCase;

class LastLoginRepositoryTest extends DatabaseTestCase
{
    /**
     * @var LastLoginRepository
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
        $this->repository = $this->getLastLoginRepository();
        $this->repository->setNow(new DateTime('2018-01-01'));
    }

    public function test_it_reads_deprovision_candidates()
    {
        $candidates = $this->repository->findDeprovisionCandidates(2);
        $this->assertInstanceOf(LastLoginCollectionInterface::class, $candidates);
        // see the database fixture for more details on the last login set we are querying
        $this->assertEquals(3, $candidates->count());
    }

    public function test_deprovision_candidates_returns_empty_collection()
    {
        $this->repository->setNow(new DateTime('1900-01-01'));
        $candidates = $this->repository->findDeprovisionCandidates(2);
        $this->assertInstanceOf(LastLoginCollectionInterface::class, $candidates);
        // see the database fixture for more details on the last login set we are querying
        $this->assertEquals(0, $candidates->count());
    }

    public function test_delete()
    {
        $userId ='urn:collab:person:surf.nl:jason_mraz';
        $result = $this->repository->delete($userId);

        $this->assertNull($result, 'The delete message should return void.');
        $this->assertCount(3, $this->repository->findAll());
    }

    public function test_delete_non_exisiting()
    {
        $userId ='urn:collab:person:surf.nl:joe_dirt';
        $result = $this->repository->delete($userId);

        $this->assertNull($result, 'The delete message should return void.');
        $this->assertCount(4, $this->repository->findAll());
    }
}
