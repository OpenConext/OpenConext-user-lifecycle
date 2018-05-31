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

namespace OpenConext\UserLifecycle\Tests\Integration\UserLifecycleBundle\Command;

use DateTime;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use OpenConext\UserLifecycle\Application\Service\DeprovisionService;
use OpenConext\UserLifecycle\Domain\Entity\LastLogin;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Command\DeprovisionCommand;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Repository\LastLoginRepository;
use OpenConext\UserLifecycle\Tests\Integration\DatabaseTestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BatchDeprovisionCommandTest extends DatabaseTestCase
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var MockHandler
     */
    private $handlerMyService;

    /**
     * @var MockHandler
     */
    private $handlerMySecondService;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var LastLoginRepository
     */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->container = self::$kernel->getContainer();

        // Create a client collection that consists of mockable guzzle clients utilizing the Guzzle mock handler.
        $clientCollection = $this->container->get('open_conext.user_lifecycle.test.deprovision_client_collection');

        $clientCollection->addClient(
            $this->container->get('open_conext.user_lifecycle.deprovision_client.test.my_service_name')
        );
        $clientCollection->addClient(
            $this->container->get('open_conext.user_lifecycle.deprovision_client.test.my_second_name')
        );

        // Expose the mock handlers, so the test methods can determine what the 'api' should return
        $this->handlerMyService = $this->container->get(
            'open_conext.user_lifecycle.guzzle_mock_handler.my_service_name'
        );
        $this->handlerMySecondService = $this->container->get(
            'open_conext.user_lifecycle.guzzle_mock_handler.my_second_name'
        );

        // Create the application and add the information command
        $this->application = new Application(self::$kernel);

        $deprovisionService = $this->container->get(DeprovisionService::class);

        // Set the time on the LastLoginRepository
        $this->repository = $this->container->get('doctrine.orm.default_entity_manager')->getRepository(LastLogin::class);
        $this->repository->setNow(new DateTime('2018-01-01'));

        $logger = m::mock(LoggerInterface::class);
        $logger->shouldIgnoreMissing();

        $this->application->add(new DeprovisionCommand($deprovisionService, $logger));

        // Load the database fixtures
        $this->loadFixtures();
    }

    public function test_execute()
    {
        // Ascertain we start of with 4 entries in the last login repository
        $this->assertCount(4, $this->repository->findAll());

        $collabPersonId = 'urn:collab:org:surf.nl:jimi_hendrix';
        $this->handlerMyService->append(
            new Response(200, [], $this->getOkStatus('my_service_name', $collabPersonId))
        );

        $this->handlerMySecondService->append(
            new Response(200, [], $this->getOkStatus('my_second_name', $collabPersonId))
        );

        $command = $this->application->find('user-lifecycle:deprovision');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertContains($collabPersonId, $output);
        $this->assertContains('OK', $output);

        // After deprovisioning the user should have been removed from the last login table
        $this->assertCount(3, $this->repository->findAll());
    }

    public function test_execute_multiple_users()
    {
        // Set the repository time in the future, ensuring deprovisioning of all users in the last login table
        $this->repository->setNow(new DateTime('2028-01-01'));

        // Ascertain we start of with 4 entries in the last login repository
        $this->assertCount(4, $this->repository->findAll());

        $this->handlerMyService->append(
            new Response(200, [], $this->getOkStatus('my_service_name', 'user1')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'user2')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'user3')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'user4'))
        );

        $this->handlerMySecondService->append(
            new Response(200, [], $this->getOkStatus('my_second_name', 'user1')),
            new Response(200, [], $this->getOkStatus('my_second_name', 'user2')),
            new Response(200, [], $this->getOkStatus('my_second_name', 'user3')),
            new Response(200, [], $this->getOkStatus('my_second_name', 'user4'))
        );

        $command = $this->application->find('user-lifecycle:deprovision');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);
        $commandTester->execute([]);

        // After deprovisioning the user should have been removed from the last login table
        $this->assertCount(0, $this->repository->findAll());
    }
    public function test_execute_multiple_users_one_failure()
    {
        // Set the repository time in the future, ensuring deprovisioning of all users in the last login table
        $this->repository->setNow(new DateTime('2028-01-01'));

        // Ascertain we start of with 4 entries in the last login repository
        $this->assertCount(4, $this->repository->findAll());

        $this->handlerMyService->append(
            new Response(200, [], $this->getOkStatus('my_service_name', 'user1')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'user2')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'user3')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'user4'))
        );

        $this->handlerMySecondService->append(
            new Response(200, [], $this->getOkStatus('my_second_name', 'user1')),
            new Response(200, [], $this->getOkStatus('my_second_name', 'user2')),
            new Response(200, [], $this->getFailedStatus('my_second_name', 'user3')),
            new Response(200, [], $this->getOkStatus('my_second_name', 'user4'))
        );

        $command = $this->application->find('user-lifecycle:deprovision');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);
        $commandTester->execute([]);

        // After deprovisioning the user should have been removed from the last login table
        $this->assertCount(1, $this->repository->findAll());
    }

    private function getOkStatus($serviceName, $collabPersonId)
    {
        return sprintf(
            '{"status": "OK", "name": "%s", "data": [ { "name": "foobar", "value": "%s" } ] }',
            $serviceName,
            $collabPersonId
        );
    }

    private function getFailedStatus($serviceName, $collabPersonId)
    {
        return sprintf(
            '{"status": "FAILED", "message": "Something went awfully wrong", "name": "%s", ' .
            '"data": [ { "name": "foobar", "value": "%s" } ] }',
            $serviceName,
            $collabPersonId
        );
    }
}
