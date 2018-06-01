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

use OpenConext\UserLifecycle\Application\Command\RemoveFromLastLoginCommand;
use OpenConext\UserLifecycle\Application\CommandHandler\RemoveFromLastLoginCommandHandler;
use OpenConext\UserLifecycle\Domain\Client\BatchInformationResponseCollection;
use OpenConext\UserLifecycle\Domain\Client\BatchInformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\DeprovisionClientCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Service\DeprovisionServiceInterface;
use OpenConext\UserLifecycle\Domain\Service\LastLoginServiceInterface;
use OpenConext\UserLifecycle\Domain\Service\RemovalCheckServiceInterface;
use OpenConext\UserLifecycle\Domain\Service\SanityCheckServiceInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class DeprovisionService implements DeprovisionServiceInterface
{
    /**
     * @var DeprovisionClientCollectionInterface
     */
    private $deprovisionClientCollection;

    /**
     * @var SanityCheckServiceInterface
     */
    private $sanityCheckService;

    /**
     * @var LastLoginServiceInterface
     */
    private $lastLoginService;

    /**
     * @var RemovalCheckServiceInterface
     */
    private $removalCheckService;

    /**
     * @var RemoveFromLastLoginCommandHandler
     */
    private $removeFromLastLoginCommandHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DeprovisionClientCollectionInterface $deprovisionClientCollection,
        SanityCheckServiceInterface $sanityCheckService,
        LastLoginServiceInterface $lastLoginService,
        RemovalCheckServiceInterface $removalCheckService,
        RemoveFromLastLoginCommandHandler $removeFromLastLoginCommandHandler,
        LoggerInterface $logger
    ) {
        $this->deprovisionClientCollection = $deprovisionClientCollection;
        $this->sanityCheckService = $sanityCheckService;
        $this->lastLoginService = $lastLoginService;
        $this->removalCheckService = $removalCheckService;
        $this->removeFromLastLoginCommandHandler = $removeFromLastLoginCommandHandler;
        $this->logger = $logger;
    }

    /**
     * @param string $personId
     * @param bool $dryRun
     * @return InformationResponseCollectionInterface
     */
    public function deprovision($personId, $dryRun = false)
    {
        $this->logger->debug('Received a request to deprovision a user.');

        $collabPersonId = $this->buildCollabPersonId($personId);

        $this->logger->debug('Delegate deprovisioning to the registered services.');

        $information = $this->deprovisionClientCollection->deprovision($collabPersonId, $dryRun);

        $this->logger->info(
            sprintf('Received deprovision information for user "%s" with the following data.', $personId),
            ['information_response' => json_encode($information)]
        );

        if (!$dryRun && $this->removalCheckService->mayBeRemoved($information)) {
            $this->logger->info('Succesfully deprovisioned the user from the services he used.');
            $command = new RemoveFromLastLoginCommand($collabPersonId);
            $this->logger->info('Remove the user from the last login table.');
            $this->removeFromLastLoginCommandHandler->handle($command);
        }

        return $information;
    }

    /**
     * Finds the users marked for deprovisioning, and deprovisions them.
     *
     * @param bool $dryRun
     * @return BatchInformationResponseCollectionInterface
     */
    public function batchDeprovision($dryRun = false)
    {
        $this->logger->debug('Retrieve the users that are marked for deprovisioning.');
        $users = $this->lastLoginService->findUsersForDeprovision();

        $this->logger->debug('Perform sanity checks on the response from the last login service.');
        $this->sanityCheckService->check($users);

        $batchInformationCollection = new BatchInformationResponseCollection();
        foreach ($users->getData() as $lastLogin) {
            $collabPersonId = $this->buildCollabPersonId($lastLogin->getCollabPersonId());
            $information = $this->deprovisionClientCollection->deprovision($collabPersonId, $dryRun);
            $batchInformationCollection->add($collabPersonId, $information);

            $this->logger->info(
                sprintf(
                    'Received deprovision information for user "%s" with the following data.',
                    $lastLogin->getCollabPersonId()
                ),
                ['information_response' => json_encode($information)]
            );

            if (!$dryRun && $this->removalCheckService->mayBeRemoved($information)) {
                $this->logger->info('Succesfully deprovisioned the user from the services he used.');
                $command = new RemoveFromLastLoginCommand($collabPersonId);
                $this->logger->info('Remove the user from the last login table.');
                $this->removeFromLastLoginCommandHandler->handle($command);
            }
        }

        return $batchInformationCollection;
    }

    private function buildCollabPersonId($personId)
    {
        Assert::stringNotEmpty($personId, 'Please pass a non empty collabPersonId');

        return new CollabPersonId($personId);
    }
}
