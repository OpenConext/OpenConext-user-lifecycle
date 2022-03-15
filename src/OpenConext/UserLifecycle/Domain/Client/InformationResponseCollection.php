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

use function array_key_exists;

class InformationResponseCollection implements InformationResponseCollectionInterface
{
    /**
     * @var InformationResponseInterface[]
     */
    private $data = [];

    public function addInformationResponse(InformationResponseInterface $informationResponse): void
    {
        $this->data[] = $informationResponse;
    }

    /**
     * @return InformationResponseInterface[]
     */
    public function getInformationResponses(): array
    {
        return $this->data;
    }

    /**
     * @return string[]
     */
    public function getErrorMessages(): array
    {
        $messages = [];
        foreach ($this->data as $entry) {
            if ($entry->getErrorMessage() && $entry->getErrorMessage()->hasErrorMessage()) {
                $messages[(string) $entry->getName()] = (string) $entry->getErrorMessage();
            }
        }
        return $messages;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function successesPerClient(): array
    {
        $report = [];
        foreach ($this->data as $entry) {
            if (is_null($entry->getErrorMessage()) || !$entry->getErrorMessage()->hasErrorMessage()) {
                $clientName = (string) $entry->getName();
                if (!array_key_exists($clientName, $report)) {
                    $report[$clientName] = 0;
                }
                $report[$clientName]++;
            }
        }
        return $report;
    }
}
