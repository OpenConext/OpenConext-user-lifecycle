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
use OpenConext\UserLifecycle\Application\QueryHandler\LastLoginByUserIdQueryHandlerInterface;
use OpenConext\UserLifecycle\Application\Service\InformationService;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollection;
use OpenConext\UserLifecycle\Domain\Entity\LastLogin;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class InformationServiceTest extends TestCase
{
    /**
     * @var InformationService
     */
    private $service;

    /**
     * @var DeprovisionClientCollectionInterface|Mock
     */
    private $apiCollection;

    public function setUp()
    {
        $this->apiCollection = m::mock(DeprovisionClientCollectionInterface::class);
        $logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->service = new InformationService($this->apiCollection, $logger);
    }

    public function test_read_information_for()
    {
        // Setup the test using test doubles
        $personId = 'jay-leno';

        $collection = m::mock(InformationResponseCollection::class);
        $collection
            ->shouldReceive('jsonSerialize')
            ->andReturn('{"only": "test"}');

        $this->apiCollection
            ->shouldReceive('information')
            ->andReturn($collection);

        // Call the readInformationFor method
        $response = $this->service->readInformationFor($personId);
        $this->assertJson($response);
    }

    public function test_read_information_empty_person_id()
    {
        // Setup the test using test doubles
        $personId = '';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please pass a non empty collabPersonId');

        $this->service->readInformationFor($personId);
    }
}
