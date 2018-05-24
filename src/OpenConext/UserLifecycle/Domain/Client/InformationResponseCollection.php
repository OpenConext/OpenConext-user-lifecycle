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

use OpenConext\UserLifecycle\Domain\ValueObject\Client\ErrorMessage;

class InformationResponseCollection implements InformationResponseCollectionInterface
{
    /**
     * @var InformationResponseInterface[]
     */
    private $data = [];

    public function addInformationResponse(InformationResponseInterface $informationResponse)
    {
        $this->data[(string) $informationResponse->getName()] = $informationResponse;
    }

    /**
     * @return InformationResponseInterface[]
     */
    public function getInformationResponses()
    {
        return $this->data;
    }

    /**
     * @return ErrorMessage[]
     */
    public function getErrorMessages()
    {
        $messages = [];
        foreach ($this->data as $entry) {
            if ($entry->getErrorMessage() && $entry->getErrorMessage()->hasErrorMessage()) {
                $messages[(string) $entry->getName()] = (string) $entry->getErrorMessage();
            }
        }
        return $messages;
    }

    public function jsonSerialize()
    {
        return json_encode($this->data, JSON_PRETTY_PRINT);
    }
}
