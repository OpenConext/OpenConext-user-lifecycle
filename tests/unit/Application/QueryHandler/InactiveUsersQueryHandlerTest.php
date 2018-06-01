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

namespace OpenConext\UserLifecycle\Tests\Unit\Application\QueryHandler;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Mock;
use OpenConext\UserLifecycle\Application\Query\InactiveUsersQuery;
use OpenConext\UserLifecycle\Application\QueryHandler\InactiveUsersQueryHandler;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use OpenConext\UserLifecycle\Domain\Repository\LastLoginRepositoryInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\InactivityPeriod;
use PHPUnit\Framework\TestCase;

class InactiveUsersQueryHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var InactiveUsersQueryHandler
     */
    private $queryHandler;

    /**
     * @var LastLoginRepositoryInterface|Mock
     */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(LastLoginRepositoryInterface::class);
        $this->queryHandler = new InactiveUsersQueryHandler($this->repository);
    }

    public function test_handle()
    {

        $period = m::mock(InactivityPeriod::class);
        $query = new InactiveUsersQuery($period);

        $collection = m::mock(LastLoginCollectionInterface::class);

        $this->repository
            ->shouldReceive('findDeprovisionCandidates')
            ->with($period)
            ->andReturn($collection);

        $expectedCollection = $this->queryHandler->handle($query);

        $this->assertEquals($expectedCollection, $collection);
    }
}
