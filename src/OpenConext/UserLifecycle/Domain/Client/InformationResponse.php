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

namespace OpenConext\UserLifecycle\Domain\Client;

use OpenConext\UserLifecycle\Domain\ValueObject\Client\Data;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ErrorMessage;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Name;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ResponseStatus;

class InformationResponse implements InformationResponseInterface
{
    public function __construct(
        private readonly ResponseStatus $status,
        private readonly Name           $name,
        private readonly Data           $data,
        private readonly ?ErrorMessage  $errorMessage = null,
    ) {
    }

    public function getStatus(): ResponseStatus
    {
        return $this->status;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getData(): Data
    {
        return $this->data;
    }

    public function getErrorMessage(): ErrorMessage
    {
        return $this->errorMessage;
    }

    public function jsonSerialize(): array
    {
        $response = [
            'name' => (string) $this->getName(),
            'status' => (string) $this->getStatus(),
            'data' => $this->getData()->getData(),
        ];

        if ($this->getErrorMessage()->hasErrorMessage()) {
            $response['message'] = (string) $this->getErrorMessage();
        }

        return $response;
    }
}
