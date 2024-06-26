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

namespace OpenConext\UserLifecycle\Domain\ValueObject\Client;

use OpenConext\UserLifecycle\Domain\Exception\InvalidDataException;

class Data
{
    public const VALID_DATA_FIELD_NAME = 'name';
    public const VALID_DATA_FIELD_VALUE = 'value';

    private readonly array $data;

    public function __construct(
        array $data,
    ) {
        if (!empty($data)) {
            foreach ($data as $entry) {
                $this->assertValidEntry($entry);
            }
        }

        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Tests if the entry is valid. If not an exception is thrown.
     * @throws InvalidDataException
     */
    private function assertValidEntry(
        array $entry,
    ): void {
        if (!array_key_exists(self::VALID_DATA_FIELD_NAME, $entry) ||
            !array_key_exists(self::VALID_DATA_FIELD_VALUE, $entry)
        ) {
            throw new InvalidDataException('Expected one of: "name", "value"');
        }
    }
}
