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
use OpenConext\UserLifecycle\Application\Service\DeprovisionService;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Client\DeprovisionClientCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeprovisionServiceTest extends TestCase
{
    /**
     * @var DeprovisionService
     */
    private $service;

    /**
     * @var DeprovisionClientCollection|Mock
     */
    private $apiCollection;

    public function setUp()
    {
        $this->apiCollection = m::mock(DeprovisionClientCollection::class);
        $logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->service = new DeprovisionService($this->apiCollection, $logger);
    }

    public function test_deprovision()
    {
        // Setup the test using test doubles
        $personId = 'jay-leno';
        $collection = m::mock(InformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('jsonSerialize')
            ->andReturn('{"only": "test"}');

        $this->apiCollection
            ->shouldReceive('deprovision')
            ->andReturnUsing(function ($expectedCollabPersonId, $expectedDryRunState) use ($collection) {
                $this->assertEquals('jay-leno', $expectedCollabPersonId->getCollabPersonId());
                $this->assertFalse($expectedDryRunState);
                return $collection;
            });

        // Call the readInformationFor method
        $response = $this->service->deprovision($personId);

        $this->assertJson($response);
    }

    public function test_deprovision_dry_run()
    {
        // Setup the test using test doubles
        $personId = 'jeff-beck';
        $collection = m::mock(InformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('jsonSerialize')
            ->andReturn('{"only": "test"}');

        $this->apiCollection
            ->shouldReceive('deprovision')
            ->andReturnUsing(function ($expectedCollabPersonId, $expectedDryRunState) use ($collection) {
                $this->assertEquals('jeff-beck', $expectedCollabPersonId->getCollabPersonId());
                $this->assertTrue($expectedDryRunState);
                return $collection;
            });

        // Call the readInformationFor method
        $response = $this->service->deprovision($personId, true);

        $this->assertJson($response);
    }

    public function test_deprovision_empty_person_id()
    {
        // Setup the test using test doubles
        $personId = '';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please pass a non empty collabPersonId');

        $this->service->deprovision($personId);
    }
}
