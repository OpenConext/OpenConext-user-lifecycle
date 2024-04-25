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

namespace OpenConext\UserLifecycle\Tests\Integration\UserLifecycleBundle\Command;

use DateTime;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use OpenConext\UserLifecycle\Application\Service\DeprovisionService;
use OpenConext\UserLifecycle\Application\Service\ProgressReporter;
use OpenConext\UserLifecycle\Application\Service\SummaryService;
use OpenConext\UserLifecycle\Domain\Entity\LastLogin;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Command\DeprovisionCommand;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Repository\LastLoginRepository;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Service\Stopwatch;
use OpenConext\UserLifecycle\Tests\Integration\DatabaseTestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Stopwatch\Stopwatch as FrameworkStopwatch;

class BatchDeprovisionCommandTest extends DatabaseTestCase
{
    /**
     * @var ContainerInterface
     */
    protected static $container;

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

    protected function setUp(): void
    {
        parent::setUp();
        self::$container = self::$kernel->getContainer();

        // Create a client collection that consists of mockable guzzle clients utilizing the Guzzle mock handler.
        $clientCollection = self::$container->get('open_conext.user_lifecycle.test.deprovision_client_collection');

        $clientCollection->addClient(
            self::$container->get('open_conext.user_lifecycle.deprovision_client.test.my_service_name'),
        );
        $clientCollection->addClient(
            self::$container->get('open_conext.user_lifecycle.deprovision_client.test.my_second_name'),
        );

        // Expose the mock handlers, so the test methods can determine what the 'api' should return
        $this->handlerMyService = self::$container->get(
            'open_conext.user_lifecycle.guzzle_mock_handler.my_service_name',
        );
        $this->handlerMySecondService = self::$container->get(
            'open_conext.user_lifecycle.guzzle_mock_handler.my_second_name',
        );

        // Create the application and add the information command
        $this->application = new Application();


        $deprovisionService = self::$container->get(DeprovisionService::class);

        $progressReporter = new ProgressReporter(
            new Stopwatch(new FrameworkStopwatch()),
            m::mock(LoggerInterface::class)->shouldIgnoreMissing(),
        );
        $summaryService = new SummaryService($progressReporter);

        // Set the time on the LastLoginRepository
        $this->repository = self::$container
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository(LastLogin::class);
        $this->repository->setNow(new DateTime('2018-01-01'));

        $logger = m::mock(LoggerInterface::class);
        $logger->shouldIgnoreMissing();


        $this->application->add(
            new DeprovisionCommand($deprovisionService, $summaryService, $progressReporter, $logger),
        );

        // Load the database fixtures
        $this->loadFixtures();
    }

    public function test_execute(): void
    {
        // Ascertain we start of with 4 entries in the last login repository
        $this->assertCount(4, $this->repository->findAll());

        $collabPersonId = 'urn:collab:person:surf.nl:jimi_hendrix';
        $this->handlerMyService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getOkStatus('my_service_name', $collabPersonId)),
        );

        $this->handlerMySecondService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getOkStatus('my_second_name', $collabPersonId)),
        );

        $command = $this->application->find('deprovision');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString($collabPersonId, $output);
        $this->assertStringContainsString('OK', $output);
        $this->assertEquals(0, $commandTester->getStatusCode());
        // After deprovisioning the user should have been removed from the last login table
        $this->assertCount(3, $this->repository->findAll());
    }

    public function test_execute_multiple_users(): void
    {
        // Set the repository time in the future, ensuring deprovisioning of all users in the last login table
        $this->repository->setNow(new DateTime('2028-01-01'));

        // Ascertain we start of with 4 entries in the last login repository
        $this->assertCount(4, $this->repository->findAll());

        $this->handlerMyService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getOkStatus('my_service_name', 'urn:collab:person:surf.nl:james_watson')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'urn:collab:person:surf.nl:john_doe')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'urn:collab:person:surf.nl:jimi_hendrix')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'urn:collab:person:surf.nl:jason_mraz')),
        );

        $this->handlerMySecondService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getOkStatus('my_second_name', 'urn:collab:person:surf.nl:james_watson')),
            new Response(200, [], $this->getOkStatus('my_second_name', 'urn:collab:person:surf.nl:jimi_hendrix')),
            new Response(200, [], $this->getOkStatus('my_second_name', 'urn:collab:person:surf.nl:jason_mraz')),
            new Response(200, [], $this->getOkStatus('my_second_name', 'urn:collab:person:surf.nl:john_doe')),
        );

        $command = $this->application->find('deprovision');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();

        // The deprovision reporting should show correct data
        $this->assertStringContainsString('"runtime":0', $output);
        $this->assertStringContainsString('"last-login-removals":4', $output);
        $this->assertStringContainsString(
            '"deprovisioned-per-client":{"my_service_name":4,"my_second_name":4}',
            $output,
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        // After deprovisioning the user should have been removed from the last login table
        $this->assertCount(0, $this->repository->findAll());
    }

    public function test_execute_multiple_users_one_failure(): void
    {
        // Set the repository time in the future, ensuring deprovisioning of all users in the last login table
        $this->repository->setNow(new DateTime('2028-01-01'));

        // Ascertain we start of with 4 entries in the last login repository
        $this->assertCount(4, $this->repository->findAll());

        $this->handlerMyService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getOkStatus('my_service_name', 'urn:collab:person:user1')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'urn:collab:person:user2')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'urn:collab:person:user3')),
            new Response(200, [], $this->getOkStatus('my_service_name', 'urn:collab:person:user4')),
        );

        $this->handlerMySecondService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getOkStatus('my_second_name', 'urn:collab:person:user1')),
            new Response(200, [], $this->getOkStatus('my_second_name', 'urn:collab:person:user2')),
            new Response(200, [], $this->getFailedStatus('my_second_name', 'urn:collab:person:user3')),
            new Response(200, [], $this->getOkStatus('my_second_name', 'urn:collab:person:user4')),
        );

        $command = $this->application->find('deprovision');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs(['yes']);
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertEquals(1, $commandTester->getStatusCode());
        // After deprovisioning the user should have been removed from the last login table
        $this->assertCount(1, $this->repository->findAll());
        $this->assertStringContainsString(
            '"Something went awfully wrong" for user "urn:collab:person:surf.nl:jason_mraz"',
            $output,
        );
    }

    private function getOkStatus(
        $serviceName,
        $collabPersonId,
    ): string {
        return sprintf(
            '{"status": "OK", "name": "%s", "data": [ { "name": "foobar", "value": "%s" } ] }',
            $serviceName,
            $collabPersonId,
        );
    }

    private function getFailedStatus(
        $serviceName,
        $collabPersonId,
    ): string {
        return sprintf(
            '{"status": "FAILED", "message": "Something went awfully wrong", "name": "%s", ' .
            '"data": [ { "name": "foobar", "value": "%s" } ] }',
            $serviceName,
            $collabPersonId,
        );
    }
}
