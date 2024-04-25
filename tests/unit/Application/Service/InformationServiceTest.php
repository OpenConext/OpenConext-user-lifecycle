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

use InvalidArgumentException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Mock;
use OpenConext\UserLifecycle\Domain\Service\DeprovisionClientHealthCheckerInterface;
use OpenConext\UserLifecycle\Application\Service\InformationService;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollection;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Client\DeprovisionClientCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InformationServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var InformationService
     */
    private $service;

    /**
     * @var DeprovisionClientCollectionInterface|Mock
     */
    private $apiCollection;

    protected function setUp(): void
    {
        $this->apiCollection = m::mock(
            DeprovisionClientCollection::class,
            DeprovisionClientHealthCheckerInterface::class,
        );
        $this->apiCollection
            ->shouldReceive('healthCheck');
        $logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->service = new InformationService($this->apiCollection, $logger);
    }

    public function test_read_information_for(): void
    {
        // Setup the test using test doubles
        $personId = 'urn:collab:person:jay-leno';

        $collection = m::mock(InformationResponseCollection::class);
        $collection
            ->shouldReceive('jsonSerialize')
            ->andReturn(["only" => "test"]);

        $this->apiCollection
            ->shouldReceive('information')
            ->andReturn($collection);

        // Call the readInformationFor method
        $response = $this->service->readInformationFor($personId);
        $this->assertInstanceOf(InformationResponseCollection::class, $response);
    }

    public function test_read_information_empty_person_id(): void
    {
        // Setup the test using test doubles
        $personId = '';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please pass a non empty collabPersonId');

        $this->service->readInformationFor($personId);
    }
}
