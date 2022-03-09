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

namespace OpenConext\UserLifecycle\Application\Command;

use OpenConext\UserLifecycle\Domain\Service\ProgressReporterInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;

class RemoveFromLastLoginCommand implements CommandInterface
{
    private $collabPersonId;
    private $progressReporter;

    public function __construct(CollabPersonId $personId, ProgressReporterInterface $progressReporter)
    {
        $this->collabPersonId = $personId;
        $this->progressReporter = $progressReporter;
    }

    public function getCollabPersonId(): CollabPersonId
    {
        return $this->collabPersonId;
    }

    public function getProgressReporter(): ProgressReporterInterface
    {
        return $this->progressReporter;
    }
}
