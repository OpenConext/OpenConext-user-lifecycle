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

use OpenConext\UserLifecycle\Application\Query\InactiveUsersQuery;
use OpenConext\UserLifecycle\Application\QueryHandler\InactiveUsersQueryHandlerInterface;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use OpenConext\UserLifecycle\Domain\Service\LastLoginServiceInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\InactivityPeriod;
use Psr\Log\LoggerInterface;

class LastLoginService implements LastLoginServiceInterface
{
    public function __construct(
        private readonly InactivityPeriod $inactivityPeriod,
        private readonly InactiveUsersQueryHandlerInterface $queryHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Search for users that are up for deprovisioning
     */
    public function findUsersForDeprovision(): LastLoginCollectionInterface
    {
        $this->logger->debug(
            sprintf(
                'Received a request to find deprovision candidates with inactivity period of %d months.',
                $this->inactivityPeriod->getInactivityPeriodInMonths(),
            ),
        );
        $query = new InactiveUsersQuery($this->inactivityPeriod);
        return $this->queryHandler->handle($query);
    }
}
