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

namespace OpenConext\UserLifecycle\Tests\Unit\Application\CommandHandler;

use Mockery as m;
use Mockery\Mock;
use OpenConext\UserLifecycle\Application\Command\RemoveFromLastLoginCommand;
use OpenConext\UserLifecycle\Application\CommandHandler\RemoveFromLastLoginCommandHandler;
use OpenConext\UserLifecycle\Application\Query\InactiveUsersQuery;
use OpenConext\UserLifecycle\Application\QueryHandler\InactiveUsersQueryHandler;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use OpenConext\UserLifecycle\Domain\Repository\LastLoginRepositoryInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use OpenConext\UserLifecycle\Domain\ValueObject\InactivityPeriod;
use PHPUnit\Framework\TestCase;

class RemoveFromLastLoginCommandHandlerTest extends TestCase
{
    /**
     * @var RemoveFromLastLoginCommandHandler
     */
    private $commandHandler;

    /**
     * @var LastLoginRepositoryInterface|Mock
     */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(LastLoginRepositoryInterface::class);
        $this->commandHandler = new RemoveFromLastLoginCommandHandler($this->repository);
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function test_handle()
    {
        $collabPersonId = m::mock(CollabPersonId::class);
        $collabPersonId
            ->shouldReceive('getCollabPersonId')
            ->andReturn('urn:collab:org:surf.nl:james_carter');
        $query = new RemoveFromLastLoginCommand($collabPersonId);

        $this->repository
            ->shouldReceive('delete')
            ->with('urn:collab:org:surf.nl:james_carter')
            ->andReturnNull();

        $this->assertNull($this->commandHandler->handle($query));
    }
}
