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

namespace OpenConext\UserLifecycle\Domain\Repository;

use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\InactivityPeriod;

interface LastLoginRepositoryInterface
{

    public function findDeprovisionCandidates(
        InactivityPeriod $inactivityPeriod,
    ): LastLoginCollectionInterface;

    /**
     * Delete an entry from the last login table identified by collabPersonId
     */
    public function delete(
        string $collabPersonId,
    );
}
