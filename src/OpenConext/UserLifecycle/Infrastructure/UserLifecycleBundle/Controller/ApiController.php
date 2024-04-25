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

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Controller;

use OpenConext\UserLifecycle\Application\Query\FindUserInformationQuery;
use OpenConext\UserLifecycle\Application\QueryHandler\FindUserInformationQueryHandlerInterface;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Api\DeprovisionApiFeatureToggle;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Webmozart\Assert\Assert;

class ApiController extends AbstractController
{
    public function __construct(
        private readonly DeprovisionApiFeatureToggle $apiFeatureToggle,
        private readonly FindUserInformationQueryHandlerInterface $queryHandler,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(
        path: '/api/deprovision/{collabPersonId}',
        name: 'deprovision',
        requirements: ['collabPersonId' => '.+'],
        methods: ['GET'],
    )]
    public function deprovision(
        string $collabPersonId,
    ): JsonResponse {
        $this->logger->debug('Received an API request for user information');

        Assert::stringNotEmpty($collabPersonId, 'Received invalid collabPersonId.');
        if ($this->apiFeatureToggle->isEnabled()) {
            $query = new FindUserInformationQuery($collabPersonId);
            $response = $this->queryHandler->handle($query);

            return $this->json($response);
        }

        $this->logger->debug('The deprovision API is not enabled, showing a 404 page instead.');
        throw $this->createNotFoundException('Page not found!');
    }
}
