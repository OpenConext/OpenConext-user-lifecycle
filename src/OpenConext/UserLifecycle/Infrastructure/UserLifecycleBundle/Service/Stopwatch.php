<?php

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

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Service;

use DateTime;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\ORM\EntityRepository;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollection;
use OpenConext\UserLifecycle\Domain\Repository\LastLoginRepositoryInterface;
use OpenConext\UserLifecycle\Domain\Service\StopwatchInterface;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\DatabaseConnectionException;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\RuntimeException;
use Symfony\Component\Stopwatch\Stopwatch as SymfonyStopwatch;
use Webmozart\Assert\Assert;

class Stopwatch implements StopwatchInterface
{
    private const TIMER = 'timer';
    /**
     * @var SymfonyStopwatch
     */
    private $stopwatch;

    private $isStarted = false;
    private $isStopped = false;

    public function __construct(SymfonyStopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    public function start(): void
    {
        $this->isStarted = true;
        $this->stopwatch->start(self::TIMER);
    }

    public function stop(): void
    {
        if (!$this->isStarted) {
            throw new RuntimeException("Unable to stop a stopwatch that was not started");
        }
        $this->isStopped = true;
        $this->stopwatch->stop(self::TIMER);
    }

    public function elapsedTime(): float
    {
        if (!$this->isStarted) {
            throw new RuntimeException("Unable to get the elapsed time of a stopwatch that was not started");
        }
        if (!$this->isStopped) {
            throw new RuntimeException("First stop the stopwatch before getting elapsed time");
        }

        return $this->stopwatch->getEvent(self::TIMER)->getDuration();
    }
}
