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

use OpenConext\UserLifecycle\Domain\Exception\InvalidResponseStatusException;

class ResponseStatus
{
    const STATUS_OK = 'OK';
    const STATUS_FAILED = 'FAILED';

    /**
     * @var string
     */
    private $status;

    /**
     * @param string $status
     */
    public function __construct($status)
    {
        if (!is_string($status) || empty(trim($status))) {
            throw new InvalidResponseStatusException(
                'ResponseStatus must be of the type string, and can not be empty.'
            );
        }
        $validStatuses = [ResponseStatus::STATUS_FAILED, ResponseStatus::STATUS_OK];

        if (!in_array($status, $validStatuses)) {
            throw new InvalidResponseStatusException('The ResponseStatus must be either "OK" or "FAILED".');
        }

        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function __toString()
    {
        return $this->getStatus();
    }
}
