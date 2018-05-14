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
use Mockery\Mock;
use OpenConext\UserLifecycle\Application\QueryHandler\LastLoginByUserIdQueryHandler;
use OpenConext\UserLifecycle\Domain\Entity\LastLogin;
use OpenConext\UserLifecycle\Domain\Query\LastLoginByUserIdQuery;
use OpenConext\UserLifecycle\Domain\Repository\LastLoginRepositoryInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use PHPUnit\Framework\TestCase;

class LastLoginUserIdQueryHandlerTest extends TestCase
{
    /**
     * @var LastLoginByUserIdQueryHandler
     */
    private $queryHandler;

    /**
     * @var LastLoginRepositoryInterface|Mock
     */
    private $repository;

    public function setUp()
    {
        $this->repository = m::mock(LastLoginRepositoryInterface::class);
        $this->queryHandler = new LastLoginByUserIdQueryHandler($this->repository);
    }

    public function test_handle()
    {
        $result = m::mock(LastLogin::class);

        $person = new CollabPersonId('john_doe');
        $query = new LastLoginByUserIdQuery($person);

        $this->repository
            ->shouldReceive('findLastLoginFor')
            ->with($person)
            ->andReturn($result);

        $lastLogin = $this->queryHandler->handle($query);

        $this->assertEquals($result, $lastLogin);
    }

    public function test_handle_not_exists()
    {
        $person = new CollabPersonId('johny_cash');
        $query = new LastLoginByUserIdQuery($person);

        $this->repository
            ->shouldReceive('findLastLoginFor')
            ->with($person)
            ->andReturn(null);

        $lastLogin = $this->queryHandler->handle($query);

        $this->assertNull($lastLogin);
    }
}
