<?php

declare(strict_types = 1);

/**
 * Copyright 2022 SURFnet B.V.
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

use JsonSerializable;

class DeprovisionStatistics implements JsonSerializable
{
    private int $runtime = 0;

    /**
     * @var array<string, int>
     */
    private array $deprovisionedPerClient = [];
    private int $lastLoginRemovals = 0;

    public function addDeprovisionedPerClient(
        array $deprovisionStatistics,
    ): void {
        foreach ($deprovisionStatistics as $serviceName => $numberOfRemovals) {
            if (!array_key_exists($serviceName, $this->deprovisionedPerClient)) {
                $this->deprovisionedPerClient[$serviceName] = 0;
            }
            $this->deprovisionedPerClient[$serviceName] += $numberOfRemovals;
        }
    }

    public function addLastLoginRemoval(): void
    {
        $this->lastLoginRemovals++;
    }

    /**
     * Runtime should be in seconds
     */
    public function setRuntime(
        int $runtime,
    ): void {
        $this->runtime = $runtime;
    }

    /**
     * @return array<string, int|array<string, int>>
     */
    public function jsonSerialize(): array
    {
        return [
            'runtime' => $this->runtime,
            'deprovisioned-per-client' => $this->deprovisionedPerClient,
            'last-login-removals' => $this->lastLoginRemovals
        ];
    }
}
