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

namespace OpenConext\UserLifecycle\Application\Service;

use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Service\RemovalCheckServiceInterface;
use Psr\Log\LoggerInterface;

class RemovalCheckService implements RemovalCheckServiceInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Analyze a collection and report if deprovisioning was a success
     */
    public function mayBeRemoved(
        InformationResponseCollectionInterface $collection,
    ): bool {
        if (empty($collection->getErrorMessages())) {
            $this->logger->debug('The user may be deprovisioned.');
            return true;
        }
        $this->logger->debug('The user may not be deprovisioned.');
        return false;
    }
}
