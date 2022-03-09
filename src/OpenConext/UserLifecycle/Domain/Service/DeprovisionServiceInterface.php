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

use OpenConext\UserLifecycle\Domain\Client\BatchInformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;

interface DeprovisionServiceInterface
{
    /**
     * @param string $personId
     * @param bool $dryRun
     * @return InformationResponseCollectionInterface
     */
    public function deprovision(ProgressReporterInterface $progressReporter, $personId, $dryRun = false);

    /**
     * Finds the users marked for deprovisioning, and deprovisions them.
     *
     * @param ProgressReporterInterface $progressReporter
     * @param bool $dryRun
     * @return BatchInformationResponseCollectionInterface
     */
    public function batchDeprovision(ProgressReporterInterface $progressReporter, $dryRun = false);
}
