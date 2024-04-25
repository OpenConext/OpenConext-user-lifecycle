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

namespace OpenConext\UserLifecycle\Tests\Unit\Domain\Client;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollection;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Data;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ErrorMessage;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Name;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ResponseStatus;
use PHPUnit\Framework\TestCase;

class InformationResponseCollectionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_can_be_created(): void
    {
        $collection = new InformationResponseCollection();
        $this->assertEmpty($collection->getInformationResponses());
        $this->assertEmpty($collection->getErrorMessages());
    }

    public function test_can_be_set_with_information_responses(): void
    {
        $info1 = $this->buildMockInformationResponse('OK', 'engine', [['name' => 'user', 'value' => 'JK']]);
        $info2 = $this->buildMockInformationResponse('OK', 'teams', [['name' => 'user', 'value' => 'JK']]);

        $collection = new InformationResponseCollection();
        $collection->addInformationResponse($info1);
        $collection->addInformationResponse($info2);

        $this->assertCount(2, $collection->getInformationResponses());
        $this->assertEmpty($collection->getErrorMessages());
    }

    public function test_it_sets_last_error_message(): void
    {
        $info1 = $this->buildMockInformationResponse(
            'FAILED',
            'teams',
            [['name' => 'user', 'value' => 'JK']],
            'Service unavailable',
        );
        $info2 = $this->buildMockInformationResponse('OK', 'engine', [['name' => 'user', 'value' => 'JK']]);

        $collection = new InformationResponseCollection();
        $collection->addInformationResponse($info1);
        $collection->addInformationResponse($info2);

        $this->assertCount(2, $collection->getInformationResponses());
        $errorMessages = $collection->getErrorMessages();

        $this->assertArrayHasKey('teams', $errorMessages);
        $this->assertEquals('Service unavailable', $errorMessages['teams']);
    }

    private function buildMockInformationResponse(
        $status,
        $name,
        $data,
        $errorMessage = null,
    ) {
        $informationResponse = m::mock(InformationResponseInterface::class);

        $statusMock = m::mock(ResponseStatus::class);
        $statusMock
            ->shouldReceive('getStatus')
            ->andReturn($status);

        $nameMock = m::mock(Name::class);
        $nameMock
            ->shouldReceive('getName')
            ->andReturn($name);
        $nameMock
            ->shouldReceive('__toString')
            ->andReturn($name);

        $dataMock = m::mock(Data::class);
        $dataMock
            ->shouldReceive('getData')
            ->andReturn($data);


        $errorMessageMock = m::mock(ErrorMessage::class);
        $hasErrorMessage = false;

        if ($errorMessage) {
            $errorMessageMock
                ->shouldReceive('getErrorMessage')
                ->andReturn($errorMessage);

            $errorMessageMock
                ->shouldReceive('__toString')
                ->andReturn($errorMessage);

            $hasErrorMessage = true;
        }

        $errorMessageMock
            ->shouldReceive('hasErrorMessage')
            ->andReturn($hasErrorMessage);

        $informationResponse->shouldReceive('getStatus')->andReturn($statusMock);
        $informationResponse->shouldReceive('getName')->andReturn($nameMock);
        $informationResponse->shouldReceive('getData')->andReturn($dataMock);
        $informationResponse->shouldReceive('getErrorMessage')->andReturn($errorMessageMock);

        return $informationResponse;
    }
}
