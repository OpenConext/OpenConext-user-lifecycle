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

namespace OpenConext\UserLifecycle\Tests\Unit\Domain\ValueObject;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\UserLifecycle\Domain\Exception\InvalidCollabPersonIdException;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use PHPUnit\Framework\TestCase;

class CollabPersonIdTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_can_be_created()
    {
        $user = new CollabPersonId('urn:mace:example.com:jan');
        $this->assertEquals('urn:mace:example.com:jan', $user->getCollabPersonId());
    }

    /**
     * @dataProvider invalidArguments
     * @param $invalidArgument
     */
    public function test_must_be_non_empty_string($invalidArgument)
    {
        $this->expectException(InvalidCollabPersonIdException::class);
        $this->expectExceptionMessage('The collabUserId must be a non empty string');

        new CollabPersonId($invalidArgument);
    }

    public function invalidArguments()
    {
        return [
            [''],
            [' '],
            [[]],
            [null],
            [true],
            [['urn:mace:example.com:jan']]
        ];
    }
}
