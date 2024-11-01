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

namespace OpenConext\UserLifecycle\Application\QueryHandler;

use OpenConext\UserLifecycle\Application\Query\FindUserInformationQuery;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseInterface;
use OpenConext\UserLifecycle\Domain\Service\InformationServiceInterface;

class FindUserInformationQueryHandler implements FindUserInformationQueryHandlerInterface
{
    public function __construct(
        private readonly InformationServiceInterface $informationService,
    ) {
    }

    public function handle(
        FindUserInformationQuery $query,
    ): InformationResponseCollectionInterface {
        return $this->informationService->readInformationFor($query->getCollabPersonId());
    }
}
