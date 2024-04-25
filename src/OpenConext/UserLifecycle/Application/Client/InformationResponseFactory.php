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

namespace OpenConext\UserLifecycle\Application\Client;

use Exception;
use InvalidArgumentException;
use OpenConext\UserLifecycle\Domain\Client\InformationResponse;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseFactoryInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Data;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ErrorMessage;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Name;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ResponseStatus;
use Webmozart\Assert\Assert;

class InformationResponseFactory implements InformationResponseFactoryInterface
{
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
    public function fromApiResponse(
        array $response,
    ): InformationResponse {
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
        $errorMessage = $this->buildErrorMessage($response, $status);

        return new InformationResponse($status, $name, $data, $errorMessage);
    }

    /**
     * Build an InformationResponse object from an exception.
     *
     * @param Exception $exception
     * @return InformationResponse
     */
    public function fromException(
        Exception $exception,
        $applicationName,
    ): InformationResponse {
        $status = new ResponseStatus(ResponseStatus::STATUS_FAILED);
        $name = new Name($applicationName);
        $data = new Data([]);

        $errorMessage = new ErrorMessage(
            $exception->getMessage(),
        );

        return new InformationResponse($status, $name, $data, $errorMessage);
    }

    private function buildErrorMessage(
        array $response,
        ResponseStatus $status,
    ): ErrorMessage {
        // If status failed, we need an error message
        if ($status->getStatus() === ResponseStatus::STATUS_FAILED) {
            Assert::notEmpty($response['message']);
        }

        // If status OK, we do not want an error message
        if ($status->getStatus() === ResponseStatus::STATUS_OK && isset($response['message'])) {
            Assert::nullOrIsEmpty($response['message']);
        }

        if (isset($response['message']) && !empty($response['message'])) {
            return new ErrorMessage($response['message']);
        }
        // If the message is not set, return an empty error message object
        return new ErrorMessage();
    }
}
