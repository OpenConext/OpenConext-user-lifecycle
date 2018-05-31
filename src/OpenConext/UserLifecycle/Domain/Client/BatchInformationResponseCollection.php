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

use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;

class BatchInformationResponseCollection implements BatchInformationResponseCollectionInterface
{
    /**
     * @var InformationResponseCollectionInterface[]
     */
    private $data = [];

    public function add(CollabPersonId $personId, InformationResponseCollectionInterface $collection)
    {
        $this->data[$personId->getCollabPersonId()] = $collection;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * Get an array of error messages in string format
     * @return string[]
     */
    public function getErrorMessages()
    {
        $messages = [];
        foreach ($this->data as $collabPersonId => $collection) {
            $errorMessages = $collection->getErrorMessages();
            if (!empty($errorMessages)) {
                foreach ($errorMessages as $message) {
                    $messages[] = sprintf('"%s" for user "%s"', $message, $collabPersonId);
                }
            }
        }
        return $messages;
    }
}
