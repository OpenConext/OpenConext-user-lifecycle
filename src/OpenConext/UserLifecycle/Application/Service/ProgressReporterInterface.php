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

use OpenConext\UserLifecycle\Domain\Service\ProgressReporterInterface as BaseProgressReporterInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface ProgressReporterInterface extends BaseProgressReporterInterface
{
    public function setConsoleOutput(
        OutputInterface $output,
    ): void;

    public function progress(
        string $message,
        int $total,
        int $current,
    ): void;
}
