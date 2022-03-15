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
use OpenConext\UserLifecycle\Application\Service\ProgressReporter;
use OpenConext\UserLifecycle\Application\Service\SummaryService;
use OpenConext\UserLifecycle\Domain\Client\BatchInformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use PHPUnit\Framework\TestCase;

class SummaryServiceTest extends TestCase
{
    /**
     * @var SummaryService
     */
    private $service;

    /**
     * @var m\Mock|ProgressReporter
     */
    private $reporter;

    protected function setUp(): void
    {
        $this->reporter = m::mock(ProgressReporter::class);
        $this->service = new SummaryService($this->reporter);
    }

    public function test_summarize_information_collection()
    {
        $collection = m::mock(InformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('count')
            ->andReturn(5);

        $collection
            ->shouldReceive('getErrorMessages')
            ->andReturn([]);

        $summary = $this->service->summarizeDeprovisionResponse($collection);

        $this->assertStringContainsString('The user was removed from 5 services.', $summary);
        $this->assertStringNotContainsString('See error messages below:', $summary);
    }

    public function test_summarize_information_collection_with_errors()
    {
        $collection = m::mock(InformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('count')
            ->andReturn(5);

        $collection
            ->shouldReceive('getErrorMessages')
            ->andReturn(['EngineBlock' => 'Fake error message']);

        $summary = $this->service->summarizeDeprovisionResponse($collection);

        $this->assertStringContainsString('The user was removed from 5 services.', $summary);
        $this->assertStringContainsString('See error messages below:', $summary);
        $this->assertStringContainsString(' * EngineBlock: Fake error message', $summary);
    }

    public function test_summarize_batch_information_collection()
    {
        $collection = m::mock(BatchInformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('count')
            ->andReturn(256);

        $collection
            ->shouldReceive('getErrorMessages')
            ->andReturn([]);

        $this->reporter
            ->shouldReceive('printDeprovisionStatistics');

        $summary = $this->service->summarizeBatchResponse($collection);

        $this->assertStringNotContainsString('See error messages below:', $summary);
    }

    public function test_summarize_batch_information_collection_with_errors()
    {
        $collection = m::mock(BatchInformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('count')
            ->andReturn(256);

        $collection
            ->shouldReceive('getErrorMessages')
            ->andReturn([
                'Service name: "Error message" for user "collabPersonId"',
                'EngineBlock: "Service not available" for user "urn:collab:jane_doe"',
                'DropjesService: "Server has gone away" for user "urn:collab:jack_black"',
            ]);

        $this->reporter
            ->shouldReceive('printDeprovisionStatistics');

        $summary = $this->service->summarizeBatchResponse($collection);

        $this->assertStringContainsString(
            '3 deprovision calls to services failed. See error messages below:',
            $summary
        );
        $this->assertStringContainsString(' * Service name: "Error message" for user "collabPersonId"', $summary);
        $this->assertStringContainsString(
            ' * EngineBlock: "Service not available" for user "urn:collab:jane_doe"',
            $summary
        );
        $this->assertStringContainsString(
            ' * DropjesService: "Server has gone away" for user "urn:collab:jack_black"',
            $summary
        );
    }

    public function test_summarize_information_collection_information_context()
    {

        $collection = m::mock(InformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('count')
            ->andReturn(5);

        $collection
            ->shouldReceive('getErrorMessages')
            ->andReturn([]);

        $summary = $this->service->summarizeInformationResponse($collection);

        $this->assertStringContainsString('Retrieved user information from 5 services.', $summary);
        $this->assertStringNotContainsString('See error messages below:', $summary);
    }
}
