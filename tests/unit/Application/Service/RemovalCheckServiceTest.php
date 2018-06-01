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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Mock;
use OpenConext\UserLifecycle\Application\Service\RemovalCheckService;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ErrorMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RemovalCheckServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var RemovalCheckService
     */
    private $service;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    public function setUp()
    {
        $this->logger = m::mock(LoggerInterface::class);
        $this->service = new RemovalCheckService($this->logger);
    }

    public function test_may_be_removed()
    {

        $this->logger
            ->shouldReceive('debug')
            ->with('The user may be deprovisioned.')
            ->once();

        $collection = m::mock(InformationResponseCollectionInterface::class);

        $collection
            ->shouldReceive('getErrorMessages')
            ->andReturn([]);

        $this->assertTrue($this->service->mayBeRemoved($collection));
    }

    public function test_may_not_be_removed()
    {

        $this->logger
            ->shouldReceive('debug')
            ->with('The user may not be deprovisioned.')
            ->once();

        $collection = m::mock(InformationResponseCollectionInterface::class);

        $collection
            ->shouldReceive('getErrorMessages')
            ->andReturn([m::mock(ErrorMessage::class)]);

        $this->assertFalse($this->service->mayBeRemoved($collection));
    }
}
