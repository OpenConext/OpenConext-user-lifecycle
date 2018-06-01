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
use OpenConext\UserLifecycle\Application\Service\InformationService;
use OpenConext\UserLifecycle\Domain\Service\InformationServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InformationCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var InformationService
     */
    private $service;


    public function __construct(InformationServiceInterface $informationService, LoggerInterface $logger)
    {
        parent::__construct(null);
        $this->service = $informationService;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('user-lifecycle:information')
            ->setDescription('Read privacy information for a given user identified by a collabPersonId.')
            ->setHelp(
                'This command allows you to read information of a given user identified by a collabPersonId. ' .
                'The command will ask all registered applications what information is available for the user.'
            )
            ->addArgument(
                'user',
                InputArgument::REQUIRED,
                'The collabPersonId of the user to deprovision.'
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
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userIdInput = $input->getArgument('user');
        $this->logger->info(sprintf('Received an information request for user: "%s"', $userIdInput));
        try {
            $output->write($this->service->readInformationFor($userIdInput));
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
