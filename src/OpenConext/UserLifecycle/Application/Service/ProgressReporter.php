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

use OpenConext\UserLifecycle\Domain\Service\StopwatchInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\DeprovisionStatistics;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressReporter implements ProgressReporterInterface
{
    private ?OutputInterface $output = null;

    private readonly DeprovisionStatistics $deprovisionStatistics;

    public function __construct(
        private readonly StopwatchInterface $stopwatch,
        private readonly LoggerInterface $logger,
    ) {
        $this->deprovisionStatistics = new DeprovisionStatistics();
    }

    public function setConsoleOutput(
        OutputInterface $output,
    ): void {
        $this->output = $output;
    }

    public function progress(
        string $message,
        int $total,
        int $current,
    ): void {
        if ($this->output === null) {
            return;
        }

        $progress = floor($current / $total * 100);

        $paddedProgress = str_pad(
            sprintf(
                '[%s%% of %d users]',
                str_pad((string) $progress, 3, ' ', STR_PAD_LEFT),
                $total,
            ),
            20,
            ' ',
        );

        $this->output->writeln(
            sprintf('%s %s', $paddedProgress, $message),
        );
    }

    public function startStopwatch(): void
    {
        $this->stopwatch->start();
    }

    public function stopStopwatch(): void
    {
        $this->stopwatch->stop();
    }

    public function reportDeprovisionedFromService(
        array $statistics,
    ): void {
        $this->deprovisionStatistics->addDeprovisionedPerClient($statistics);
    }

    public function reportRemovedFromLastLogin(): void
    {
        $this->deprovisionStatistics->addLastLoginRemoval();
    }

    public function printDeprovisionStatistics(): void
    {
        $this->deprovisionStatistics->setRuntime(
            // Convert milliseconds to seconds as an integer, rounded down
            // to keep behaviour. Maybe we should round() instead?
            // This was an int casting, but that is deprecated in PHP 8.0
            (int) floor($this->stopwatch->elapsedTime() / 1000),
        );

        $stats = json_encode($this->deprovisionStatistics->jsonSerialize());
        $this->logger->info($stats);
        $this->output->writeln($stats);
    }
}
