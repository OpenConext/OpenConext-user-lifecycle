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

namespace OpenConext\UserLifecycle\Tests\Unit\Domain\ValueObject\Client;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseInterface;
use OpenConext\UserLifecycle\Domain\Exception\InvalidDataException;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Data;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_build_empty()
    {
        $data = new Data([]);
        $this->assertEmpty($data->getData());
    }

    public function test_add_entry()
    {
        $entry = ['name' => 'collabPersonId', 'value' => 'urn:collab:person:jesse.james'];
        $entryTeams = ['name' => 'teams', 'value' => 'admins'];
        $data = new Data([$entry, $entryTeams]);
        $this->assertEquals([$entry, $entryTeams], $data->getData());
    }

    public function test_it_reject_invalid_data_expects_array_of_entries()
    {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage('The data must be of the type array');
        new Data(['name' => 'my name', 'value' => 'my value']);
    }

    public function test_it_reject_invalid_data_expects_valid_entries()
    {
        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage('Expected one of: "name", "value"');
        new Data([['key' => 'my name', 'value' => 'my value']]);
    }
}
