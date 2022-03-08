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

namespace OpenConext\UserLifecycle\Tests\Unit\Application\Service;

use InvalidArgumentException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Mock;
use OpenConext\UserLifecycle\Application\CommandHandler\RemoveFromLastLoginCommandHandler;
use OpenConext\UserLifecycle\Application\Service\DeprovisionService;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use OpenConext\UserLifecycle\Domain\Entity\LastLogin;
use OpenConext\UserLifecycle\Domain\Service\LastLoginServiceInterface;
use OpenConext\UserLifecycle\Domain\Service\ProgressReporterInterface;
use OpenConext\UserLifecycle\Domain\Service\RemovalCheckServiceInterface;
use OpenConext\UserLifecycle\Domain\Service\SanityCheckServiceInterface;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Client\DeprovisionClientCollection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class DeprovisionServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var DeprovisionService
     */
    private $service;

    /**
     * @var DeprovisionClientCollection|Mock
     */
    private $apiCollection;

    /**
     * @var SanityCheckServiceInterface|Mock
     */
    private $sanityChecker;

    /**
     * @var LastLoginServiceInterface|Mock
     */
    private $lastLoginService;

    /**
     * @var RemovalCheckServiceInterface|Mock
     */
    private $removalCheckService;

    /**
     * @var RemoveFromLastLoginCommandHandler|Mock
     */
    private $removeFromLastLoginCommandHandler;

    protected function setUp(): void
    {
        $this->apiCollection = m::mock(DeprovisionClientCollection::class);
        $this->sanityChecker = m::mock(SanityCheckServiceInterface::class);
        $this->lastLoginService = m::mock(LastLoginServiceInterface::class);
        $this->removalCheckService = m::mock(RemovalCheckServiceInterface::class);
        $this->removeFromLastLoginCommandHandler = m::mock(RemoveFromLastLoginCommandHandler::class);
        $logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();
        $this->service = new DeprovisionService(
            $this->apiCollection,
            $this->sanityChecker,
            $this->lastLoginService,
            $this->removalCheckService,
            $this->removeFromLastLoginCommandHandler,
            $logger
        );
    }

    public function test_deprovision()
    {
        // Setup the test using test doubles
        $personId = 'urn:collab:person:jay-leno';
        $collection = m::mock(InformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('jsonSerialize')
            ->andReturn('{"only": "test"}');

        $this->apiCollection
            ->shouldReceive('deprovision')
            ->andReturnUsing(
                function ($expectedCollabPersonId, $expectedDryRunState) use ($collection) {
                    $this->assertEquals('urn:collab:person:jay-leno', $expectedCollabPersonId->getCollabPersonId());
                    $this->assertFalse($expectedDryRunState);

                    return $collection;
                }
            );

        $this->removalCheckService
            ->shouldReceive('mayBeRemoved')
            ->once()
            ->andReturn(true);

        $this->removeFromLastLoginCommandHandler
            ->shouldReceive('handle')
            ->once();

        // Call the readInformationFor method
        $response = $this->service->deprovision($personId);

        $this->assertInstanceOf(InformationResponseCollectionInterface::class, $response);
    }

    public function test_deprovision_dry_run()
    {
        // Setup the test using test doubles
        $personId = 'urn:collab:person:jeff-beck';
        $collection = m::mock(InformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('jsonSerialize')
            ->andReturn('{"only": "test"}');

        $this->apiCollection
            ->shouldReceive('deprovision')
            ->andReturnUsing(function ($expectedCollabPersonId, $expectedDryRunState) use ($collection) {
                $this->assertEquals('urn:collab:person:jeff-beck', $expectedCollabPersonId->getCollabPersonId());
                $this->assertTrue($expectedDryRunState);
                return $collection;
            });

        // Call the readInformationFor method
        $response = $this->service->deprovision($personId, true);

        $this->assertInstanceOf(InformationResponseCollectionInterface::class, $response);
    }

    public function test_deprovision_empty_person_id()
    {
        // Setup the test using test doubles
        $personId = '';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please pass a non empty collabPersonId');

        $this->service->deprovision($personId);
    }

    public function test_batch_deprovision()
    {
        $mockCollection = m::mock(LastLoginCollectionInterface::class);
        $mockUser1 = $this->buildMockLastLoginEntry('urn:collab:person:jack-black');
        $mockUser2 = $this->buildMockLastLoginEntry('urn:collab:person:jan-berry');

        $this->lastLoginService
            ->shouldReceive('findUsersForDeprovision')
            ->andReturn($mockCollection);

        $this->sanityChecker
            ->shouldReceive('check')
            ->with($mockCollection)
            ->andReturnNull();

        $mockCollection
            ->shouldReceive('getData')
            ->andReturn([$mockUser1, $mockUser2]);

        $mockCollection
            ->shouldReceive('count')
            ->andReturn(2);

        $collection = m::mock(InformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('jsonSerialize')
            ->andReturn('{"only": "test"}');

        $this->apiCollection
            ->shouldReceive('deprovision')
            ->andReturnUsing(
                function ($expectedCollabPersonId, $expectedDryRunState) use ($mockCollection, $collection) {
                    $this->assertContains(
                        $expectedCollabPersonId->getCollabPersonId(),
                        ['urn:collab:person:jack-black', 'urn:collab:person:jan-berry']
                    );
                    $this->assertFalse($expectedDryRunState);

                    return $collection;
                }
            );

        $this->removalCheckService
            ->shouldReceive('mayBeRemoved')
            ->twice()
            ->andReturn(true);

        $this->removeFromLastLoginCommandHandler
            ->shouldReceive('handle')
            ->twice();

        $progressReporter = m::mock(ProgressReporterInterface::class);
        $progressReporter->shouldReceive('setConsoleOutput');
        $progressReporter->shouldReceive('progress')
            ->times(3);

        $this->service->batchDeprovision($progressReporter);
    }

    public function test_batch_deprovision_dry_run()
    {
        $mockCollection = m::mock(LastLoginCollectionInterface::class);
        $mockUser1 = $this->buildMockLastLoginEntry('urn:collab:person:jack-black');
        $mockUser2 = $this->buildMockLastLoginEntry('urn:collab:person:jan-berry');

        $this->lastLoginService
            ->shouldReceive('findUsersForDeprovision')
            ->andReturn($mockCollection);

        $this->sanityChecker
            ->shouldReceive('check')
            ->with($mockCollection)
            ->andReturnNull();

        $mockCollection
            ->shouldReceive('getData')
            ->andReturn([$mockUser1, $mockUser2]);

        $mockCollection
            ->shouldReceive('count')
            ->andReturn(2);

        $collection = m::mock(InformationResponseCollectionInterface::class);
        $collection
            ->shouldReceive('jsonSerialize')
            ->andReturn('{"only": "test"}');

        $this->apiCollection
            ->shouldReceive('deprovision')
            ->andReturnUsing(
                function ($expectedCollabPersonId, $expectedDryRunState) use ($mockCollection, $collection) {
                    $this->assertContains(
                        $expectedCollabPersonId->getCollabPersonId(),
                        ['urn:collab:person:jack-black', 'urn:collab:person:jan-berry']
                    );
                    $this->assertTrue($expectedDryRunState);

                    return $collection;
                }
            );


        $progressReporter = m::mock(ProgressReporterInterface::class);
        $progressReporter->shouldReceive('setConsoleOutput');
        $progressReporter->shouldReceive('progress')
            ->times(3);

        $this->service->batchDeprovision($progressReporter, true);
    }

    private function buildMockLastLoginEntry($personId)
    {
        $lastLogin = m::mock(LastLogin::class);
        $lastLogin
            ->shouldReceive('getCollabPersonId')
            ->andReturn($personId);

        return $lastLogin;
    }
}
