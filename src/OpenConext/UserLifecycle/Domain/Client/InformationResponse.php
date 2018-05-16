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
use JsonSerializable;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Data;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ErrorMessage;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Name;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ResponseStatus;
use Webmozart\Assert\Assert;

class InformationResponse implements JsonSerializable
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

    private function __construct(ResponseStatus $status, Name $name, Data $data, ErrorMessage $errorMessage = null)
    {
        $this->status = $status;
        $this->name = $name;
        $this->data = $data;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Build an InformationResponse object from api response
     *
     * Input validation is applied based on there rules:
     *  - name must be set (non empty string)
     *  - status can be OK or FAILED (non empty string)
     *  - data must be an array filled with at least name and value keys
     *  - message must be set if status FAILED and must not be set if status OK
     *
     * @throws InvalidArgumentException
     */
    public static function fromApiResponse(array $response)
    {
        // Test if the required fields are set in the response
        $requiredFields = ['name', 'status', 'data'];
        $errorMessage = null;

        foreach ($requiredFields as $field) {
            Assert::keyExists($response, $field);
        }

        Assert::isArray($response['data']);

        // Build the individual value objects that make up the response object
        $status = new ResponseStatus($response['status']);
        $name = new Name($response['name']);
        $data = new Data($response['data']);
        $errorMessage = ErrorMessage::fromResponse($response, $status);

        return new InformationResponse($status, $name, $data, $errorMessage);
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
