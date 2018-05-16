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

use Webmozart\Assert\Assert;

class ErrorMessage
{
    /**
     * @var string|null
     */
    private $errorMessage;

    /**
     * @param string $errorMessage
     */
    private function __construct($errorMessage = null)
    {
        $this->errorMessage = $errorMessage;
    }

    public static function fromResponse(array $response, ResponseStatus $status)
    {
        // If status failed, we need an error message
        if ($status->getStatus() === ResponseStatus::STATUS_FAILED) {
            Assert::notEmpty($response['message']);
        }

        // If status OK, we do not want an error message
        if ($status->getStatus() === ResponseStatus::STATUS_OK && isset($response['message'])) {
            Assert::nullOrIsEmpty($response['message']);
        }

        if (isset($response['message']) && !empty($response['message'])) {
            return new self($response['message']);
        }
        // If the message is not set, return an empty error message object
        return new self();
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
        return $this->getErrorMessage();
    }
}
