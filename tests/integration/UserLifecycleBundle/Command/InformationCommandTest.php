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

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use OpenConext\UserLifecycle\Application\Service\InformationService;
use OpenConext\UserLifecycle\Application\Service\SummaryService;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Command\InformationCommand;
use OpenConext\UserLifecycle\Tests\Integration\DatabaseTestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class LastLoginRepositoryTest extends DatabaseTestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        self::$container = self::$kernel->getContainer();

        // Create a client collection that consists of mockable guzzle clients utilizing the Guzzle mock handler.
        $clientCollection = self::$container->get('open_conext.user_lifecycle.test.deprovision_client_collection');

        $clientCollection->addClient(
            self::$container->get('open_conext.user_lifecycle.deprovision_client.test.my_service_name')
        );
        $clientCollection->addClient(
            self::$container->get('open_conext.user_lifecycle.deprovision_client.test.my_second_name')
        );

        // Expose the mock handlers, so the test methods can determine what the 'api' should return
        $this->handlerMyService = self::$container->get(
            'open_conext.user_lifecycle.guzzle_mock_handler.my_service_name'
        );
        $this->handlerMySecondService = self::$container->get(
            'open_conext.user_lifecycle.guzzle_mock_handler.my_second_name'
        );

        // Create the application and add the information command
        $this->application = new Application();

        $lastLoginService = self::$kernel->getContainer()->get(InformationService::class);
        $summaryService = new SummaryService();

        $logger = m::mock(LoggerInterface::class);
        $logger->shouldIgnoreMissing();

        $this->application->add(new InformationCommand($lastLoginService, $summaryService, $logger));

        // Load the database fixtures
        $this->loadFixtures();
    }

    public function test_execute()
    {
        $collabPersonId = 'urn:collab:person:surf.nl:jimi_hendrix';

        $this->handlerMyService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getOkStatus('my_service_name', $collabPersonId))
        );

        $this->handlerMySecondService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getOkStatus('my_second_name', $collabPersonId))
        );

        $command = $this->application->find('information');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['user' => $collabPersonId]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($collabPersonId, $output);
        $this->assertStringContainsString('OK', $output);
    }

    public function test_execute_second_service_returned_failed_response()
    {
        $collabPersonId = 'urn:collab:person:surf.nl:jimi_hendrix';

        $this->handlerMyService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getOkStatus('my_service_name', $collabPersonId))
        );

        $errorMessage = 'Internal server error';
        $this->handlerMySecondService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getFailedStatus('my_second_name', $errorMessage))
        );
        $command = $this->application->find('information');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['user' => $collabPersonId]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($collabPersonId, $output);
        $this->assertStringContainsString('OK', $output);
        $this->assertStringContainsString('FAILED', $output);
        $this->assertStringContainsString($errorMessage, $output);
    }

    public function test_execute_no_arguments()
    {
        $this->expectExceptionMessage("Not enough arguments (missing: \"user\").");
        $this->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);
        $command = $this->application->find('information');
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
    }

    public function test_execute_silently()
    {
        $collabPersonId = 'urn:collab:person:surf.nl:jimi_hendrix';

        $this->handlerMyService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getOkStatus('my_service_name', $collabPersonId))
        );

        $errorMessage = 'Internal server error';
        $this->handlerMySecondService->append(
            new Response(200, [], '{"status":"UP"}'),
            new Response(200, [], $this->getFailedStatus('my_second_name', $errorMessage))
        );
        $command = $this->application->find('information');
        $commandTester = new CommandTester($command);

        $commandTester->execute(['user' => $collabPersonId, '--json' => true]);
        $output = $commandTester->getDisplay();
        $this->assertJson($output, 'The output must be JSON');
    }

    private function getOkStatus($serviceName, $collabPersonId)
    {
        return sprintf(
            '{"status": "OK", "name": "%s", "data": [ { "name": "foobar", "value": "%s" } ] }',
            $serviceName,
            $collabPersonId
        );
    }

    private function getFailedStatus($serviceName, $message)
    {
        return sprintf(
            '{"status": "FAILED", "name": "%s", "message": "%s", "data": []}',
            $serviceName,
            $message
        );
    }
}
