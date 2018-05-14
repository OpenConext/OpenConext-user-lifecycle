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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InformationCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('user-lifecycle:information')
            ->setDescription('Read privacy information for a given user identified by a collabPersonId.')
            ->setHelp(
                'This command allows you to read information of a given user identified by a collabPersonId. ' .
                'The command will ask all registered applications what information is available for the user.'
            )
            ->addOption(
                'user',
                'u',
                InputOption::VALUE_REQUIRED,
                'The user identifier of the user to get information from.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
