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

namespace OpenConext\UserLifecycle\Application\Service;

use OpenConext\UserLifecycle\Domain\Service\StopwatchInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\DeprovisionStatistics;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressReporter implements ProgressReporterInterface
{
    /**
     * @var StopwatchInterface
     */
    private $stopwatch;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var DeprovisionStatistics
     */
    private $deprovisionStatistics;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(StopwatchInterface $stopwatch, LoggerInterface $logger)
    {
        $this->stopwatch = $stopwatch;
        $this->deprovisionStatistics = new DeprovisionStatistics();
        $this->logger = $logger;
    }

    public function setConsoleOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param string $message
     * @param int $total
     * @param int $current
     */
    public function progress(string $message, int $total, int $current): void
    {
        if ($this->output === null) {
            return;
        }

        $progress = floor($current / $total * 100);

        $paddedProgress = str_pad(
            sprintf(
                '[%s%% of %d users]',
                str_pad((string) $progress, 3, ' ', STR_PAD_LEFT),
                $total
            ),
            20,
            ' '
        );

        $this->output->writeln(
            sprintf('%s %s', $paddedProgress, $message)
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

    public function reportDeprovisionedFromService(array $statistics): void
    {
        $this->deprovisionStatistics->addDeprovisionedPerClient($statistics);
    }

    public function reportRemovedFromLastLogin(): void
    {
        $this->deprovisionStatistics->addLastLoginRemoval();
    }

    public function printDeprovisionStatistics()
    {
        $this->deprovisionStatistics->setRuntime((int) $this->stopwatch->elapsedTime() / 1000);

        $stats = json_encode($this->deprovisionStatistics->jsonSerialize());
        $this->logger->info($stats);
        $this->output->writeln($stats);
    }
}
