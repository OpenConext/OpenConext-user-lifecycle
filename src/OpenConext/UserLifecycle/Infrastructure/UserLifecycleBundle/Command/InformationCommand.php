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
use OpenConext\UserLifecycle\Domain\Service\InformationServiceInterface;
use OpenConext\UserLifecycle\Domain\Service\SummaryServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('information')]
class InformationCommand extends Command
{
    public function __construct(
        private readonly InformationServiceInterface $service,
        private readonly SummaryServiceInterface $summaryService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Read privacy information for a given user identified by a collabPersonId.')
            ->setHelp(
                'This command allows you to read information of a given user identified by a collabPersonId. '.
                'The command will ask all registered applications what information is available for the user.',
            )
            ->addArgument(
                'user',
                InputArgument::REQUIRED,
                'The collabPersonId of the user to deprovision.',
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

    /**
     * Execute the information command
     *
     * The command will:
     *  - Retrieve information from the registered services by calling their information endpoint for the specified user
     *  - Return JSON string with the results
     *
     * In case of an error, the command will output the error in text format
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $userIdInput = $input->getArgument('user');
        $outputOnlyJson = $input->getOption('json');
        $prettyJson = $input->getOption('pretty');

        $this->logger->info(sprintf('Received an information request for user: "%s"', $userIdInput));

        try {
            $this->logger->debug('Health check the remote services.');
            $this->service->healthCheck();
            $information = $this->service->readInformationFor($userIdInput);

            if (!$outputOnlyJson) {
                $output->write($this->summaryService->summarizeInformationResponse($information), true);
            }

            $jsonOptions = 0;
            if ($prettyJson) {
                $jsonOptions |= JSON_PRETTY_PRINT;
            }

            $output->write(
                json_encode($information, $jsonOptions),
                true,
            );
        } catch (Exception $e) {
            $output->writeln(sprintf('<comment>%s</comment>', $e->getMessage()));
            $this->logger->error($e->getMessage());
            return 1;
        }
        return 0;
    }
}
