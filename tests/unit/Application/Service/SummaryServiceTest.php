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
use Mockery\Mock;
use OpenConext\UserLifecycle\Application\Service\SanityCheckService;
use OpenConext\UserLifecycle\Application\Service\SummaryService;
use OpenConext\UserLifecycle\Domain\Client\BatchInformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ErrorMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SummaryServiceTest extends TestCase
{
    /**
     * @var SummaryService
     */
    private $service;

    public function setUp()
    {
        $this->service = new SummaryService();
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

        $this->assertContains('The user was removed from 5 services.', $summary);
        $this->assertNotContains('See error messages below:', $summary);
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

        $this->assertContains('The user was removed from 5 services.', $summary);
        $this->assertContains('See error messages below:', $summary);
        $this->assertContains(' * EngineBlock: Fake error message', $summary);
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

        $summary = $this->service->summarizeBatchResponse($collection);

        $this->assertNotContains('See error messages below:', $summary);
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

        $summary = $this->service->summarizeBatchResponse($collection);

        $this->assertContains('3 deprovision calls to services failed. See error messages below:', $summary);
        $this->assertContains(' * Service name: "Error message" for user "collabPersonId"', $summary);
        $this->assertContains(' * EngineBlock: "Service not available" for user "urn:collab:jane_doe"', $summary);
        $this->assertContains(' * DropjesService: "Server has gone away" for user "urn:collab:jack_black"', $summary);
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

        $this->assertContains('Retrieved user information from 5 services.', $summary);
        $this->assertNotContains('See error messages below:', $summary);
    }
}
