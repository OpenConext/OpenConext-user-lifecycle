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

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Controller;

use OpenConext\UserLifecycle\Application\Query\FindUserInformationQuery;
use OpenConext\UserLifecycle\Application\QueryHandler\FindUserInformationQueryHandlerInterface;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Api\DeprovisionApiFeatureToggle;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

class ApiController extends AbstractController
{
    /**
     * @var DeprovisionApiFeatureToggle
     */
    private $apiFeatureToggle;

    /**
     * @var FindUserInformationQueryHandlerInterface
     */
    private $queryHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DeprovisionApiFeatureToggle $isEnabled,
        FindUserInformationQueryHandlerInterface $queryHandler,
        LoggerInterface $logger
    ) {
        $this->apiFeatureToggle = $isEnabled;
        $this->queryHandler = $queryHandler;
        $this->logger = $logger;
    }

    /**
     * @param string $collabPersonId
     * @return JsonResponse|NotFoundHttpException
     */
    public function deprovisionAction($collabPersonId)
    {
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
