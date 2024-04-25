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

namespace OpenConext\UserLifecycle\Domain\Client;

use Countable;
use JsonSerializable;

interface InformationResponseCollectionInterface extends JsonSerializable, Countable
{
    public function addInformationResponse(
        InformationResponseInterface $informationResponse,
    ): void;

    /**
     * @return InformationResponseInterface[]
     */
    public function getInformationResponses(): array;

    /**
     * @return string[]
     */
    public function getErrorMessages(): array;

    public function jsonSerialize(): array;

    public function count(): int;

    /**
     * @return array<string, int>
     */
    public function successesPerClient(): array;
}
