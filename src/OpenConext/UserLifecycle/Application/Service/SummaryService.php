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

use OpenConext\UserLifecycle\Domain\Client\BatchInformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Service\SummaryServiceInterface;
use Webmozart\Assert\Assert;

class SummaryService implements SummaryServiceInterface
{

    const USER_DEPROVISION_FORMAT = 'The user was removed from %d services.';
    const USER_DEPROVISION_ERROR_FORMAT = 'See error messages below:';

    const USER_INFORMATION_FORMAT = 'Retrieved user information from %d services.';

    const BATCH_DEPROVISION_FORMAT = '%d users have been deprovisioned.';
    const BATCH_DEPROVISION_ERROR_FORMAT = '%d deprovision calls to services failed. See error messages below:';

    const CONTEXT_INFORMATION = 'information';
    const CONTEXT_DEPROVISION = 'deprovision';

    private $context = 'deprovision';

    /**
     * Summarize the information response from a deprovision or
     * information call.
     *
     * @param
     * @return string
     */
    public function summarize($collection)
    {
        Assert::isInstanceOfAny(
            $collection,
            [InformationResponseCollectionInterface::class, BatchInformationResponseCollectionInterface::class]
        );

        if ($collection instanceof InformationResponseCollectionInterface) {
            return $this->summarizeInformationResponse($collection);
        }

        if ($collection instanceof BatchInformationResponseCollectionInterface) {
            return $this->summarizeBatchInformationResponse($collection);
        }
    }

    private function summarizeInformationResponse(InformationResponseCollectionInterface $collection)
    {
        $message = sprintf(self::USER_DEPROVISION_FORMAT, count($collection)) . PHP_EOL;
        if ($this->context === self::CONTEXT_INFORMATION) {
            $message = sprintf(self::USER_INFORMATION_FORMAT, count($collection)) . PHP_EOL;
        }

        $errorMessages = $collection->getErrorMessages();
        $errorMessageList = '';
        if (!empty($errorMessages)) {
            $errorMessageList .= sprintf(self::USER_DEPROVISION_ERROR_FORMAT) . PHP_EOL . PHP_EOL;

            foreach ($errorMessages as $errorMessage) {
                $errorMessageList .= ' * ' . $errorMessage . PHP_EOL;
            }
        }

        return $message . $errorMessageList;
    }

    private function summarizeBatchInformationResponse(BatchInformationResponseCollectionInterface $collection)
    {
        $message = sprintf(self::BATCH_DEPROVISION_FORMAT, count($collection)) . PHP_EOL;

        $errorMessageList = '';

        $errorMessages = $collection->getErrorMessages();
        if (!empty($errorMessages)) {
            $errorMessageList .= sprintf(self::BATCH_DEPROVISION_ERROR_FORMAT, count($errorMessages)) . PHP_EOL . PHP_EOL;
            foreach ($errorMessages as $errorMessage) {
                $errorMessageList .= ' * ' . $errorMessage . PHP_EOL;
            }
        }

        return $message . $errorMessageList;
    }

    /**
     * Set the context to deprovision or information outputting
     *
     * @param string $context
     */
    public function setContext($context)
    {
        Assert::stringNotEmpty($context);
        Assert::oneOf($context, [self::CONTEXT_INFORMATION, self::CONTEXT_DEPROVISION]);
        $this->context = $context;
    }
}
