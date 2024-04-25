<?php

declare(strict_types = 1);

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
use OpenConext\UserLifecycle\Application\Service\SanityCheckService;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use OpenConext\UserLifecycle\Domain\Exception\EmptyLastLoginCollectionException;
use OpenConext\UserLifecycle\Domain\Exception\InvalidLastLoginCollectionException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SanityCheckServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var SanityCheckService
     */
    private $service;

    /**
     * @var LoggerInterface|Mock
     */
    private $logger;

    protected function setUp(): void
    {
        $this->logger = m::mock(LoggerInterface::class);
        $this->service = new SanityCheckService(
            2,
            $this->logger,
        );
    }

    public function test_check(): void
    {
        $lastLoginCollection = m::mock(LastLoginCollectionInterface::class);

        $lastLoginCollection->shouldReceive('count')
            ->andReturn(2);

        $this->logger
            ->shouldReceive('debug')
            ->with('Ascertained the proposed list of deprovision candidates is valid.')
            ->once();

        $this->assertNull($this->service->check($lastLoginCollection));
    }

    public function test_check_empty(): void
    {
        $this->expectExceptionMessage("No candidates found for deprovisioning");
        $this->expectException(EmptyLastLoginCollectionException::class);
        $lastLoginCollection = m::mock(LastLoginCollectionInterface::class);

        $lastLoginCollection->shouldReceive('count')
            ->andReturn(0);

        $this->service->check($lastLoginCollection);
    }

    public function test_check_too_many(): void
    {
        $this->expectExceptionMessage(
            "Too much candidates found for deprovisioning. 100 exceeds the limit set at 2 by 98.",
        );
        $this->expectException(InvalidLastLoginCollectionException::class);
        $lastLoginCollection = m::mock(LastLoginCollectionInterface::class);

        $lastLoginCollection->shouldReceive('count')
            ->andReturn(100);

        $this->service->check($lastLoginCollection);
    }
}
