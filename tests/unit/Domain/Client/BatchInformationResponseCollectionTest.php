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

namespace OpenConext\UserLifecycle\Tests\Unit\Domain\Client;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\UserLifecycle\Domain\Client\BatchInformationResponseCollection;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollection;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Data;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ErrorMessage;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Name;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ResponseStatus;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use PHPUnit\Framework\TestCase;

class BatchInformationResponseCollectionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_can_be_created()
    {
        $collection = new BatchInformationResponseCollection();
        $this->assertEquals(0, $collection->count());
        $this->assertEmpty($collection->getErrorMessages());
    }

    public function test_it_composes_readable_error_messages()
    {
        $user1 = m::mock(CollabPersonId::class);
        $user1
            ->shouldReceive('getCollabPersonId')
            ->andReturn('JK');

        $collection = m::mock(InformationResponseCollection::class);
        $collection
            ->shouldReceive('getErrorMessages')
            ->andReturn([
                'service1' => 'service is not available',
                'service2' => 'service is not available',
                'service3' => 'service is not available',
                'service4' => 'service is not available',
            ]);

        $batchCollection = new BatchInformationResponseCollection();
        $batchCollection->add($user1, $collection);

        $this->assertEquals(1, $batchCollection->count());

        $errorMessages = $batchCollection->getErrorMessages();
        $this->assertCount(4, $errorMessages);
        $this->assertEquals('service1: "service is not available" for user "JK"', $errorMessages[0]);
        $this->assertEquals('service2: "service is not available" for user "JK"', $errorMessages[1]);
        $this->assertEquals('service3: "service is not available" for user "JK"', $errorMessages[2]);
        $this->assertEquals('service4: "service is not available" for user "JK"', $errorMessages[3]);
    }
}
