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

namespace OpenConext\UserLifecycle\Domain\ValueObject;

use InvalidArgumentException;
use JsonSerializable;
use Webmozart\Assert\Assert;

class InformationResponse implements JsonSerializable
{
    const STATUS_OK = 'OK';
    const STATUS_FAILED = 'FAILED';

    const VALID_DATA_FIELD_NAME = 'name';
    const VALID_DATA_FIELD_VALUE = 'value';

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $data;

    /**
     * @var null|string
     */
    private $errorMessage;

    private function __construct($status, $name, array $data, $errorMessage = null)
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
        $requiredFields = ['name', 'status', 'data'];
        $errorMessage = null;

        foreach ($requiredFields as $field) {
            Assert::keyExists($response, $field);
        }

        Assert::stringNotEmpty($response['name']);

        Assert::stringNotEmpty($response['status']);
        Assert::oneOf($response['status'], [self::STATUS_FAILED, self::STATUS_OK]);

        // If status failed, we need an error message
        if ($response['status'] === self::STATUS_FAILED) {
            Assert::notEmpty($response['message']);
        }

        // If status OK, we do not want an error message
        if ($response['status'] === self::STATUS_OK && isset($response['message'])) {
            Assert::nullOrIsEmpty($response['message']);
        }

        Assert::isArray($response['data']);

        if (!empty($response['data'])) {
            foreach ($response['data'] as $entry) {
                Assert::isArray($entry);
                Assert::allOneOf(array_keys($entry), [self::VALID_DATA_FIELD_NAME, self::VALID_DATA_FIELD_VALUE]);
            }
        }

        $name = $response['name'];
        $status = $response['status'];
        $data = $response['data'];

        if (isset($response['message']) && !empty($response['message'])) {
            $errorMessage = $response['message'];
        }
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

    public function hasError()
    {
        return !is_null($this->errorMessage);
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function jsonSerialize()
    {
        $response = [
            'name' => $this->getName(),
            'status' => $this->getStatus(),
            'data' => $this->getData(),
        ];

        if ($this->hasError()) {
            $response['message'] = $this->getErrorMessage();
        }

        return json_encode($response);
    }
}
