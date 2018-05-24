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

namespace OpenConext\UserLifecycle\Domain\Client;

use InvalidArgumentException;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Data;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ErrorMessage;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Name;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ResponseStatus;
use Webmozart\Assert\Assert;

class InformationResponse implements InformationResponseInterface
{
    /**
     * @var ResponseStatus
     */
    private $status;

    /**
     * @var Name
     */
    private $name;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var ErrorMessage
     */
    private $errorMessage;

    public function __construct(ResponseStatus $status, Name $name, Data $data, ErrorMessage $errorMessage = null)
    {
        $this->status = $status;
        $this->name = $name;
        $this->data = $data;
        $this->errorMessage = $errorMessage;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function jsonSerialize()
    {
        $response = [
            'name' => (string) $this->getName(),
            'status' => (string) $this->getStatus(),
            'data' => $this->getData()->getData(),
        ];

        if ($this->getErrorMessage()->hasErrorMessage()) {
            $response['message'] = (string) $this->getErrorMessage();
        }

        return json_encode($response);
    }
}
