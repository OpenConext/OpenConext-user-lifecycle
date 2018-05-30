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

namespace OpenConext\UserLifecycle\Domain\Service;

interface DeprovisionServiceInterface
{
    /**
     * @param string $personId
     * @param bool $dryRun
     * @return string
     */
    public function deprovision($personId, $dryRun = false);

    /**
     * Finds the users marked for deprovisioning, and deprovisions them.
     *
     * @param bool $dryRun
     * @return string
     */
    public function batchDeprovision($dryRun = false);
}