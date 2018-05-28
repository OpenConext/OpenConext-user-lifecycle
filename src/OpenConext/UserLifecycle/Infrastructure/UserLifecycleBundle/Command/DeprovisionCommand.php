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
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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


    public function __construct(DeprovisionServiceInterface $service, LoggerInterface $logger)
    {
        parent::__construct(null);
        $this->service = $service;
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
                'on the actually removed data.'
            )
            ->addArgument(
                'user',
                InputArgument::REQUIRED,
                'The collabPersonId of the user to deprovision.'
            )
            ->addOption(
                'dryRun',
                null,
                InputOption::VALUE_OPTIONAL,
                'Should the command be run in dry run mode?',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userIdInput = $input->getArgument('user');
        $dryRun = $input->getOption('dryRun');
        $this->logger->info(
            sprintf(
                'Received a deprovision request for user: "%s", with dryRun turned %s',
                $userIdInput,
                ($dryRun ? 'on' : 'off')
            )
        );
        try {
            $output->write($this->service->deprovision($userIdInput, $dryRun));
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
