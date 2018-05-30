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

namespace OpenConext\UserLifecycle\Tests\Unit\Application\Service;

use InvalidArgumentException;
use Mockery as m;
use Mockery\Mock;
use OpenConext\UserLifecycle\Application\Query\InactiveUsersQuery;
use OpenConext\UserLifecycle\Application\QueryHandler\InactiveUsersQueryHandler;
use OpenConext\UserLifecycle\Application\Service\LastLoginService;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LastLoginServiceTest extends TestCase
{
    /**
     * @var LastLoginService
     */
    private $service;

    /**
     * @var InactiveUsersQueryHandler|Mock
     */
    private $queryHandler;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    public function setUp()
    {
        $this->queryHandler = m::mock(InactiveUsersQueryHandler::class);
        $this->logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->service = new LastLoginService(2, $this->queryHandler, $this->logger);
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function test_read_information_for()
    {
        $this->queryHandler
            ->shouldReceive('handle')
            ->andReturnUsing(
                function (InactiveUsersQuery $query) {
                    $this->assertEquals(2,$query->getInactivityPeriod());
                    return m::mock(LastLoginCollectionInterface::class);
                }
            );

        $this->logger
            ->shouldReceive('debug')
            ->with('Received a request to find deprovision candidates with inactivity period of 2 months.')
            ->once();

        $this->service->findUsersForDeprovision();
    }
}
