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

namespace OpenConext\UserLifecycle\Tests\Unit\Application\CommandHandler;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Mock;
use OpenConext\UserLifecycle\Application\Command\RemoveFromLastLoginCommand;
use OpenConext\UserLifecycle\Application\CommandHandler\RemoveFromLastLoginCommandHandler;
use OpenConext\UserLifecycle\Domain\Repository\LastLoginRepositoryInterface;
use OpenConext\UserLifecycle\Domain\Service\ProgressReporterInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use PHPUnit\Framework\TestCase;

class RemoveFromLastLoginCommandHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private RemoveFromLastLoginCommandHandler $commandHandler;
    private LastLoginRepositoryInterface|Mock $repository;
    private ProgressReporterInterface|Mock $progressReporter;

    protected function setUp(): void
    {
        $this->repository = m::mock(LastLoginRepositoryInterface::class);
        $this->commandHandler = new RemoveFromLastLoginCommandHandler($this->repository);
        $this->progressReporter = m::mock(ProgressReporterInterface::class);
    }

    public function test_handle(): void
    {
        $collabPersonId = m::mock(CollabPersonId::class);
        $collabPersonId
            ->shouldReceive('__toString')
            ->andReturn('urn:collab:org:surf.nl:james_carter');
        $query = new RemoveFromLastLoginCommand($collabPersonId, $this->progressReporter);

        $this->repository
            ->shouldReceive('delete')
            ->with('urn:collab:org:surf.nl:james_carter')
            ->andReturnNull();

        $this->assertNull($this->commandHandler->handle($query));
    }
}
