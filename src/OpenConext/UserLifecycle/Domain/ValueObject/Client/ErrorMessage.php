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

use InvalidArgumentException;
use function is_array;
use function is_string;

class ErrorMessage
{
    /**
     * @var string|null
     */
    private $errorMessage;

    /**
     * @param string $errorMessage
     */
    public function __construct($errorMessage = null)
    {
        // Setting multiple error messages is supported, but only if all array entries are of type string
        if (is_array($errorMessage)) {
            foreach ($errorMessage as $message) {
                if (!is_string($message)) {
                    throw new InvalidArgumentException('All of the error messages must be of type string');
                }
            }
        }
        $this->errorMessage = $errorMessage;
    }

    public function hasErrorMessage()
    {
        return !is_null($this->errorMessage);
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function __toString()
    {
        $message = $this->getErrorMessage();
        if (is_array($message)) {
            $message = implode(', ', $message);
        }
        return (string) $message;
    }
}
