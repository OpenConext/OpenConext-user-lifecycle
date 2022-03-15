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

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Client;

use GuzzleHttp\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollection;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Exception\DeprovisionClientUnavailableException;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;

class DeprovisionClientCollection implements DeprovisionClientCollectionInterface
{
    /**
     * @var DeprovisionClientInterface[]
     */
    private $clients;

    /**
     * @param CollabPersonId $user
     * @param bool $dryRun
     * @return InformationResponseCollectionInterface
     */
    public function deprovision(CollabPersonId $user, $dryRun = false)
    {
        $promises = [];
        foreach ($this->clients as $client) {
            $promises[] = $client->deprovision($user, $dryRun);
        }

        return $this->collectResponses($promises);
    }

    /**
     * @param CollabPersonId $user
     * @return InformationResponseCollectionInterface
     */
    public function information(CollabPersonId $user)
    {
        $promises = [];
        foreach ($this->clients as $client) {
            $promises[] = $client->information($user);
        }

        return $this->collectResponses($promises);
    }

    public function addClient(DeprovisionClientInterface $client)
    {
        $this->clients[$client->getName()] = $client;
    }

    /**
     * @param PromiseInterface[] $promises
     * @return InformationResponseCollectionInterface
     */
    private function collectResponses(array $promises)
    {
        $collection = new InformationResponseCollection();

        $informationResponses = Promise\all($promises)
            ->wait();

        foreach ($informationResponses as $informationResponse) {
            $collection->addInformationResponse($informationResponse);
        }

        return $collection;
    }

    public function healthCheck()
    {
        foreach ($this->clients as $client) {
            $client->health();
        }
    }
}
