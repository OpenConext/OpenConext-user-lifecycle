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
use OpenConext\UserLifecycle\Domain\Exception\InformationResponseNotFoundException;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Data;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ErrorMessage;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\Name;
use OpenConext\UserLifecycle\Domain\ValueObject\Client\ResponseStatus;
use Webmozart\Assert\Assert;

/**
 * A collection of InformationResponse objects
 *
 * The data is a collection of InformationResponseCollection objects
 * stored in a Data value object. The entries are indexed on the
 * information response name of the child entry.
 *
 * By default the response status of the collection is OK, when an
 * information response with the FAILED response status is appended
 * to the collection, the status of the collection is also set to
 * FAILED, as one of it's children failed.
 *
 * The name of the collection object is hard coded to
 * 'InformationResponseCollection' as the name of the collection is
 * irrelevant for now.
 *
 * The errorMessage field will be filled with the last error message
 * that was encountered while adding an InformationResponse to the
 * collection.
 */
class InformationResponseCollection implements InformationResponseInterface
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

    public function __construct()
    {
        $this->status = new ResponseStatus(ResponseStatus::STATUS_OK);
        $this->name = new Name('InformationResponseCollection');
        $this->data = Data::buildEmpty();
    }

    public function addInformationResponse(InformationResponseInterface $informationResponse)
    {
        if ($informationResponse->getStatus()->getStatus() != ResponseStatus::STATUS_OK) {
            $this->status = $informationResponse->getStatus();
        }

        if ($informationResponse->getErrorMessage()->hasErrorMessage()) {
            $this->errorMessage = $informationResponse->getErrorMessage();
        }

        $this->data->addInformationResponse(
            (string)$informationResponse->getName(),
            $informationResponse
        );
    }

    /**
     * @param $name
     * @return InformationResponseInterface
     *
     * @throws InformationResponseNotFoundException
     */
    public function getByName($name)
    {
        foreach ($this->getData()->getData() as $entry) {
            if ($entry[Data::VALID_DATA_FIELD_NAME] == $name) {
                return $entry[Data::VALID_DATA_FIELD_VALUE];
            }
        }
        throw new InformationResponseNotFoundException(
            sprintf(
                'InformationResponse with name "%s" cannot be found in collection',
                $name
            )
        );
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
            'name' => (string)$this->getName(),
            'status' => (string)$this->getStatus(),
            'data' => $this->getData()->getData(),
        ];

        if ($this->getErrorMessage()->hasErrorMessage()) {
            $response['message'] = (string)$this->getErrorMessage();
        }

        return json_encode($response);
    }
}
