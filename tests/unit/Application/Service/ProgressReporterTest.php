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

namespace OpenConext\UserLifecycle\Tests\Unit\Application\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\UserLifecycle\Application\Service\ProgressReporter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressReporterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_reporter_does_nothing_without_console_output_object()
    {
        $reporter = new ProgressReporter();
        $this->assertNull(
            $reporter->progress('test', 1, 100)
        );
    }

    public function test_reporter_prints_progress_to_console()
    {
        $output = m::mock(OutputInterface::class);
        $output
            ->shouldReceive('writeln')
            ->with('[  0% of 4 users]    test');

        $output
            ->shouldReceive('writeln')
            ->with('[ 50% of 4 users]    test');

        $output
            ->shouldReceive('writeln')
            ->with('[100% of 4 users]    test');

        $reporter = new ProgressReporter();
        $reporter->setConsoleOutput($output);

        $reporter->progress('test', 4, 0);
        $reporter->progress('test', 4, 2);

        $this->assertNull(
            $reporter->progress('test', 4, 4)
        );
    }
}
