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

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Client;

use GuzzleHttp\Promise\Utils;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollection;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class DeprovisionClientCollection implements DeprovisionClientCollectionInterface
{
    /**
     * @var DeprovisionClientInterface[]
     */
    private ?array $clients = null;

    public function __construct(
        #[TaggedIterator('open_conext.user_lifecycle.deprovision_client')]
        /** @var $taggedClients DeprovisionClientInterface[] */
        array $taggedClients = [],
    )
    {
        foreach ($taggedClients as $taggedClient) {
            $this->addClient($taggedClient);
        }
    }

    public function deprovision(
        CollabPersonId $user,
        bool $dryRun = false,
    ): InformationResponseCollectionInterface {
        $promises = [];
        foreach ($this->clients as $client) {
            $promises[] = $client->deprovision($user, $dryRun);
        }

        return $this->collectResponses($promises);
    }

    public function information(
        CollabPersonId $user,
    ): InformationResponseCollectionInterface {
        $promises = [];
        foreach ($this->clients as $client) {
            $promises[] = $client->information($user);
        }

        return $this->collectResponses($promises);
    }

    public function addClient(
        DeprovisionClientInterface $client,
    ): void {
        $this->clients[$client->getName()] = $client;
    }

    private function collectResponses(
        array $promises,
    ): InformationResponseCollectionInterface {
        $collection = new InformationResponseCollection();

        $informationResponses = Utils::all($promises)
            ->wait();

        foreach ($informationResponses as $informationResponse) {
            $collection->addInformationResponse($informationResponse);
        }

        return $collection;
    }

    public function healthCheck(): void
    {
        foreach ($this->clients as $client) {
            $client->health();
        }
    }
}
