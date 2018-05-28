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

namespace OpenConext\UserLifecycle\Domain\ValueObject\Client;

use OpenConext\UserLifecycle\Domain\Client\InformationResponseInterface;
use OpenConext\UserLifecycle\Domain\Exception\InvalidDataException;

class Data
{
    const VALID_DATA_FIELD_NAME = 'name';
    const VALID_DATA_FIELD_VALUE = 'value';

    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (!empty($data)) {
            foreach ($data as $entry) {
                $this->assertValidEntry($entry);
            }
        }

        $this->data = $data;
    }

    public static function buildEmpty()
    {
        $instance = new self([[self::VALID_DATA_FIELD_NAME => '', self::VALID_DATA_FIELD_VALUE => '']]);
        $instance->data = [];

        return $instance;
    }

    public function getData()
    {
        return $this->data;
    }

    public function addDataEntry(array $entry)
    {
        $this->assertValidEntry($entry);
        $this->data[] = $entry;
    }

    public function addInformationResponse($name, InformationResponseInterface $informationResponse)
    {
        $entry = [
            self::VALID_DATA_FIELD_NAME => $name,
            self::VALID_DATA_FIELD_VALUE => $informationResponse,
        ];

        $this->addDataEntry($entry);
    }

    /**
     * Tests if the entry is valid. If not an exception is thrown.
     * @param $entry
     * @throws InvalidDataException
     */
    private function assertValidEntry($entry)
    {
        if (!is_array($entry)) {
            throw new InvalidDataException('The data must be of the type array');
        }
        if (!array_key_exists(self::VALID_DATA_FIELD_NAME, $entry) ||
            !array_key_exists(self::VALID_DATA_FIELD_VALUE, $entry)
        ) {
            throw new InvalidDataException('Expected one of: "name", "value"');
        }
    }
}
