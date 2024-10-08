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

namespace OpenConext\UserLifecycle\Domain\ValueObject;

use OpenConext\UserLifecycle\Domain\Exception\InvalidInactivityPeriodException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class InactivityPeriod
{
    public function __construct(
        #[Autowire(param: 'inactivity_period')]
        private readonly int $inactivityPeriodInMonths,
    ) {
        if ($inactivityPeriodInMonths <= 0) {
            throw new InvalidInactivityPeriodException('The inactivity period must be an positive integer value');
        }
    }

    public function getInactivityPeriodInMonths(): int
    {
        return $this->inactivityPeriodInMonths;
    }
}
