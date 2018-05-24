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

use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollection;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;

class DeprovisionClientCollection implements DeprovisionClientCollectionInterface
{
    /**
     * @var DeprovisionClientInterface[]
     */
    private $clients;

    public function deprovision(CollabPersonId $user, $dryRun = false)
    {
        foreach ($this->clients as $client) {
            $client->deprovision($user, $dryRun);
        }
    }

    public function information(CollabPersonId $user)
    {
        $collection = new InformationResponseCollection();
        foreach ($this->clients as $client) {
            $collection->addInformationResponse($client->information($user));
        }

        return $collection;
    }

    public function getName()
    {
        return 'DeprovisionClientCollection';
    }

    public function addClient(DeprovisionClientInterface $client)
    {
        $this->clients[$client->getName()] = $client;
    }
}
