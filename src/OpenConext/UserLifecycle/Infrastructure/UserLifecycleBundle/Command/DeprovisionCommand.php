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

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Command;

use Exception;
use JsonSerializable;
use OpenConext\UserLifecycle\Application\Service\ProgressReporterInterface;
use OpenConext\UserLifecycle\Domain\Service\DeprovisionServiceInterface;
use OpenConext\UserLifecycle\Domain\Service\SummaryServiceInterface;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand('deprovision')]
class DeprovisionCommand extends Command
{
    public function __construct(
        private readonly DeprovisionServiceInterface $service,
        private readonly SummaryServiceInterface $summaryService,
        private readonly ProgressReporterInterface $progressReporter,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Deprovision a user from the platform. The user is identified by a collabPersonId.')
            ->setHelp(
                'This command allows you to deprovision a given user identified by a collabPersonId. '.
                'The command will delegate the deprovisioning to all registered applications and report back '.
                'on the actually removed data. Optionally leave the user argument blank to deprovision all users that '.
                'meet the automatic deprovision criteria as configured with the `deprovision_after` parameter.',
            )
            ->addArgument(
                'user',
                InputArgument::OPTIONAL,
                'The collabPersonId of the user to deprovision.',
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Should the command be run in dry run mode?',
            )
            ->addOption(
                'json',
                null,
                InputOption::VALUE_NONE,
                'Output only JSON to StdOut. Requires --no-interaction to work.',
            )
            ->addOption(
                'pretty',
                null,
                InputOption::VALUE_NONE,
                'Pretty-print JSON output.',
            );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $userIdInput = $input->getArgument('user');
        $dryRun = $input->getOption('dry-run');

        $outputOnlyJson = $input->getOption('json');
        $prettyJson = $input->getOption('pretty');
        $noInteraction = $input->getOption('no-interaction');
        $this->progressReporter->startStopwatch();
        if ($outputOnlyJson && $noInteraction === false) {
            throw new RuntimeException('The --json option must be used in combination with --no-interaction (-n).');
        }

        if (is_null($userIdInput)) {
            return $this->executeBatch(
                $input,
                $output,
                $dryRun,
                $noInteraction,
                $outputOnlyJson,
                $prettyJson,
            );
        }
        return $this->executeSingleUser(
                $input,
                $output,
                $userIdInput,
                $dryRun,
                $noInteraction,
                $outputOnlyJson,
                $prettyJson,
            );
    }

    private function executeBatch(
        InputInterface $input,
        OutputInterface $output,
        $dryRun,
        $noInteraction,
        $outputOnlyJson,
        $prettyJson,
    ): int {
        $exitCode = COMMAND::SUCCESS;
        if (!$noInteraction) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                '<question>Continue with deprovisioning? (y/n)</question> ',
                false,
            );

            if (!$helper->ask($input, $output, $question)) {
                return Command::FAILURE;
            }
        }
        $this->logger->info(
            sprintf(
                'Starting batch-deprovisioning of users%s',
                ($dryRun ? ' (dry-run)' : ''),
            ),
        );

        if (!$outputOnlyJson) {
            $this->progressReporter->setConsoleOutput($output);
        }

        try {
            $this->logger->debug('Health check the remote services.');
            $this->service->healthCheck();
            $information = $this->service->batchDeprovision($this->progressReporter, $dryRun);

            // If deprovisioning yielded one or more errors, change the exit code to 1
            if (count($information->getErrorMessages()) > 0) {
                $exitCode = Command::FAILURE;
            }
            if (!$outputOnlyJson) {
                $output->writeln('');
                $output->write($this->summaryService->summarizeBatchResponse($information), true);
            }

            $this->printJson($output, $information, $prettyJson);
        } catch (Exception $e) {
            $output->writeln(sprintf('<comment>%s</comment>', $e->getMessage()));
            $this->logger->error($e->getMessage());
            return Command::FAILURE;
        }
        return $exitCode;
    }

    private function executeSingleUser(
        InputInterface $input,
        OutputInterface $output,
        $userIdInput,
        $dryRun,
        $noInteraction,
        $outputOnlyJson,
        $prettyJson,
    ): int {
        if (!$noInteraction) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                sprintf('<question>Continue with deprovisioning of "%s"? (y/n)</question> ', $userIdInput),
                false,
            );

            if (!$helper->ask($input, $output, $question)) {
                return Command::FAILURE;
            }
        }

        $this->logger->info(
            sprintf(
                'Starting deprovisioning of user "%s"%s',
                $userIdInput,
                ($dryRun ? ' (dry-run)' : ''),
            ),
        );
        try {
            $this->logger->debug('Health check the remote services.');
            $this->service->healthCheck();
            $information = $this->service->deprovision($this->progressReporter, $userIdInput, $dryRun);

            if (!$outputOnlyJson) {
                $output->write($this->summaryService->summarizeDeprovisionResponse($information), true);
            }

            $this->printJson($output, $information, $prettyJson);
        } catch (Exception $e) {
            $output->writeln(sprintf('<comment>%s</comment>', $e->getMessage()));
            $this->logger->error($e->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    private function printJson(
        OutputInterface  $output,
        JsonSerializable $information,
        bool $prettyJson,
    ): void {
        $jsonOptions = 0;

        if ($prettyJson) {
            $jsonOptions |= JSON_PRETTY_PRINT;
        }

        $output->write(
            json_encode($information, $jsonOptions),
            true,
        );
    }
}
