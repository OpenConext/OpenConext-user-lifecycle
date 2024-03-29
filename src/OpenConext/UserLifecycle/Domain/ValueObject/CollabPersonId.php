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

use OpenConext\UserLifecycle\Domain\Exception\InvalidCollabPersonIdException;

class CollabPersonId
{
    private static $pattern = '/^urn:collab:person:.+$/';

    /**
     * @var string
     */
    private $collabPersonId;

    public function __construct($collabUserId)
    {
        if (is_string($collabUserId)) {
            $collabUserId = trim($collabUserId);
        }
        if (empty($collabUserId) || !is_string($collabUserId)) {
            throw new InvalidCollabPersonIdException('The collabPersonId must be a non empty string');
        }
        if (preg_match(self::$pattern, $collabUserId) !== 1) {
            throw new InvalidCollabPersonIdException('The collabPersonId must start with urn:collab:person:');
        }
        $this->collabPersonId = $collabUserId;
    }

    /**
     * @return mixed
     */
    public function getCollabPersonId()
    {
        return $this->collabPersonId;
    }

    public function __toString()
    {
        return $this->collabPersonId;
    }
}
