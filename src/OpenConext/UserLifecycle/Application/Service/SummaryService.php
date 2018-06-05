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

class SummaryService implements SummaryServiceInterface
{

    const USER_DEPROVISION_FORMAT = 'The user was removed from %d services.';
    const USER_DEPROVISION_ERROR_FORMAT = 'See error messages below:';

    const USER_INFORMATION_FORMAT = 'Retrieved user information from %d services.';

    const BATCH_DEPROVISION_FORMAT = '%d users have been deprovisioned.';
    const BATCH_DEPROVISION_ERROR_FORMAT = '%d deprovision calls to services failed. See error messages below:';

    public function summarizeInformationResponse(InformationResponseCollectionInterface $collection)
    {
        $message = sprintf(self::USER_INFORMATION_FORMAT, count($collection)).PHP_EOL;

        $errorMessages = $collection->getErrorMessages();
        $errorMessageList = '';
        if (!empty($errorMessages)) {
            $errorMessageList .= sprintf(self::USER_DEPROVISION_ERROR_FORMAT).PHP_EOL.PHP_EOL;

            foreach ($errorMessages as $serviceName => $errorMessage) {
                $errorMessageList .= ' * '.$serviceName .': '.$errorMessage.PHP_EOL;
            }
        }

        return $message.$errorMessageList;
    }

    public function summarizeDeprovisionResponse(InformationResponseCollectionInterface $collection)
    {
        $message = sprintf(self::USER_DEPROVISION_FORMAT, count($collection)).PHP_EOL;

        $errorMessages = $collection->getErrorMessages();
        $errorMessageList = '';
        if (!empty($errorMessages)) {
            $errorMessageList .= sprintf(self::USER_DEPROVISION_ERROR_FORMAT).PHP_EOL.PHP_EOL;

            foreach ($errorMessages as $serviceName => $errorMessage) {
                $errorMessageList .= ' * '.$serviceName .': '.$errorMessage.PHP_EOL;
            }
        }

        return $message.$errorMessageList;
    }

    public function summarizeBatchResponse(BatchInformationResponseCollectionInterface $collection)
    {
        $message = sprintf(self::BATCH_DEPROVISION_FORMAT, count($collection)).PHP_EOL;

        $errorMessageList = '';

        $errorMessages = $collection->getErrorMessages();
        if (!empty($errorMessages)) {
            $errorMessageList .= sprintf(self::BATCH_DEPROVISION_ERROR_FORMAT, count($errorMessages)).PHP_EOL.PHP_EOL;
            foreach ($errorMessages as $errorMessage) {
                $errorMessageList .= ' * '.$errorMessage.PHP_EOL;
            }
        }

        return $message.$errorMessageList;
    }
}
