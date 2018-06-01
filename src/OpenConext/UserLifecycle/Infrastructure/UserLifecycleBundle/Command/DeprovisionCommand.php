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

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Command;

use InvalidArgumentException;
use OpenConext\UserLifecycle\Domain\Service\DeprovisionServiceInterface;
use OpenConext\UserLifecycle\Domain\Service\SummaryServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeprovisionCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DeprovisionServiceInterface
     */
    private $service;

    /**
     * @var SummaryServiceInterface
     */
    private $summaryService;

    public function __construct(
        DeprovisionServiceInterface $deprovisionService,
        SummaryServiceInterface $summaryService,
        LoggerInterface $logger
    ) {
        parent::__construct(null);
        $this->service = $deprovisionService;
        $this->summaryService = $summaryService;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('user-lifecycle:deprovision')
            ->setDescription('Deprovision a user from the platform. The user is identified by a collabPersonId.')
            ->setHelp(
                'This command allows you to deprovision a given user identified by a collabPersonId. '.
                'The command will delegate the deprovisioning to all registered applications and report back '.
                'on the actually removed data. Optionally leave the user argument blank to deprovision all users that '.
                'meet the automatic deprovision criteria as configured with the `deprovision_after` parameter.'
            )
            ->addArgument(
                'user',
                InputArgument::OPTIONAL,
                'The collabPersonId of the user to deprovision.'
            )
            ->addOption(
                'force',
                'f'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Should the command be run in dry run mode?'
            );
    }

    /**
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userIdInput = $input->getArgument('user');
        $dryRun = $input->getOption('dry-run');
        $forced = $input->getOption('force');
        $isQuiet = $input->getOption('quiet');

        if (is_null($userIdInput)) {
            $this->executeBatch($input, $output, $userIdInput, $dryRun, $forced, $isQuiet);
        } else {
            $this->executeSingleUser($input, $output, $userIdInput, $dryRun, $forced, $isQuiet);
        }
    }

    private function executeBatch(
        InputInterface $input,
        OutputInterface $output,
        $userIdInput,
        $dryRun,
        $forced,
        $isQuiet
    ) {
        if (!$forced) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                sprintf('<question>Continue with deprovisioning? (y/n)</question> ', $userIdInput),
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }
        $this->logger->info(
            sprintf(
                'Received a deprovision request with dryRun turned %s',
                $userIdInput,
                ($dryRun ? 'on' : 'off')
            )
        );

        try {
            $information = $this->service->batchDeprovision($dryRun);

            if (!$isQuiet) {
                $output->writeln(PHP_EOL);
                $output->write($this->summaryService->summarize($information), true);
                $output->writeln('Full output of the deprovisioning command:' . PHP_EOL);
            }

            $output->write(json_encode($information), true);
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function executeSingleUser(
        InputInterface $input,
        OutputInterface $output,
        $userIdInput,
        $dryRun,
        $forced,
        $isQuiet
    ) {
        if (!$forced) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                sprintf('<question>Continue with deprovisioning of "%s"? (y/n)</question> ', $userIdInput),
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                return;
            }
        }

        $this->logger->info(
            sprintf(
                'Received a deprovision request for user: "%s", with dryRun turned %s',
                $userIdInput,
                ($dryRun ? 'on' : 'off')
            )
        );
        try {
            $information = $this->service->deprovision($userIdInput, $dryRun);

            if (!$isQuiet) {
                $output->writeln(PHP_EOL);
                $output->write($this->summaryService->summarize($information), true);
                $output->writeln('Full output of the deprovisioning command:' . PHP_EOL);
            }

            $output->write(json_encode($information), true);
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
