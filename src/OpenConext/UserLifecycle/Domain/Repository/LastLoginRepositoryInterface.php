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

namespace OpenConext\UserLifecycle\Domain\Repository;

use OpenConext\UserLifecycle\Domain\Entity\LastLogin;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;

interface LastLoginRepositoryInterface
{
    /**
     * Finds the last login entry for a given person.
     *
     * If the entry does not exist, null is returned.
     *
     * @param CollabPersonId $collabPersonId
     * @return LastLogin|null
     */
    public function findLastLoginFor(CollabPersonId $collabPersonId);
}
