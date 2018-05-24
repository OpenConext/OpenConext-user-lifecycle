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

namespace OpenConext\UserLifecycle\Application\Service;

use InvalidArgumentException;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponse;
use OpenConext\UserLifecycle\Domain\Service\LastLoginServiceInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class LastLoginService implements LastLoginServiceInterface
{
    /**
     * @var DeprovisionClientCollectionInterface
     */
    private $deprovisionClientCollection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DeprovisionClientCollectionInterface $deprovisionClientCollection,
        LoggerInterface $logger
    ) {
        $this->deprovisionClientCollection = $deprovisionClientCollection;
        $this->logger = $logger;
    }

    /**
     * @param string $personId
     * @throws InvalidArgumentException
     * @return InformationResponse
     */
    public function readInformationFor($personId)
    {
        $this->logger->debug('Received a request for user information');

        Assert::stringNotEmpty($personId, 'Please pass a non empty collabPersonId');

        $collabPersonId = new CollabPersonId($personId);

        $this->logger->debug('Retrieve the information from the APIs for the user.');
        $information = $this->deprovisionClientCollection->information($collabPersonId);

        $this->logger->info(
            sprintf('Received information for user "%s" with the following data.', $personId),
            ['information_response' => $information]
        );

        return $information;
    }
}
