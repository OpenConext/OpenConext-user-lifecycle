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

namespace OpenConext\UserLifecycle\Tests\Unit\Infrastructure\UserLifecycleBundle\Service;

use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\RuntimeException;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Service\Stopwatch;
use PHPUnit\Framework\TestCase;
use function is_float;
use function sleep;

class StopwatchTest extends TestCase
{
    private $stopwatch;

    protected function setUp(): void
    {
        $this->stopwatch =  new Stopwatch(new \Symfony\Component\Stopwatch\Stopwatch());
    }
    public function test_it_can_start_and_stop()
    {
        $this->stopwatch->start();
        $this->stopwatch->stop();
        $this->assertTrue(is_float($this->stopwatch->elapsedTime()));
    }

    public function test_elapsed_time_returns_expected_value()
    {
        $this->stopwatch->start();
        sleep(1); // Sleep 1000 miliseconds
        $this->stopwatch->stop();
        $elapsedTime = $this->stopwatch->elapsedTime();
        $this->assertEquals(1000, $elapsedTime);
    }

    public function test_can_not_get_elapsed_time_before_started()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to get the elapsed time of a stopwatch that was not started");
        $this->stopwatch->elapsedTime();
    }

    public function test_can_not_get_elapsed_time_before_stopped()
    {
        $this->stopwatch->start();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("First stop the stopwatch before getting elapsed time");
        $this->stopwatch->elapsedTime();
    }

    public function test_can_not_stop_before_started()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Unable to stop a stopwatch that was not started");
        $this->stopwatch->stop();
    }
}
