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

namespace OpenConext\UserLifecycle\Application\Service;

use OpenConext\UserLifecycle\Domain\Client\BatchInformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Client\InformationResponseCollectionInterface;
use OpenConext\UserLifecycle\Domain\Service\SummaryServiceInterface;
use const PHP_EOL;

class SummaryService implements SummaryServiceInterface
{

    public const USER_DEPROVISION_FORMAT = 'The user was removed from %d %s.';
    public const USER_DEPROVISION_ERROR_FORMAT = 'See error messages below:';
    public const USER_DEPROVISION_JSON_HEADING = 'Full output of the deprovision command:';

    public const USER_INFORMATION_FORMAT = 'Retrieved user information from %d %s.';
    public const USER_INFORMATION_JSON_HEADING = 'Full output of the information command:';

    public const BATCH_DEPROVISION_ERROR_FORMAT = '%d deprovision calls to services failed. See error messages below:';

    public function __construct(
        private readonly ProgressReporterInterface $progressReporter,
    ) {
    }

    public function summarizeInformationResponse(
        InformationResponseCollectionInterface $collection,
    ): string {
        $count = count($collection);
        $service = $this->pluralizeService($count);
        $message = sprintf(self::USER_INFORMATION_FORMAT, $count, $service).PHP_EOL;

        $errorMessages = $collection->getErrorMessages();
        $errorMessageList = '';
        if (!empty($errorMessages)) {
            $errorMessageList .= sprintf(self::USER_DEPROVISION_ERROR_FORMAT).PHP_EOL.PHP_EOL;

            foreach ($errorMessages as $serviceName => $errorMessage) {
                $errorMessageList .= ' * '.$serviceName .': '.$errorMessage.PHP_EOL;
            }
        }

        return $message.$errorMessageList.PHP_EOL.self::USER_INFORMATION_JSON_HEADING.PHP_EOL;
    }

    public function summarizeDeprovisionResponse(
        InformationResponseCollectionInterface $collection,
    ): string {
        $count = count($collection);
        $service = $this->pluralizeService($count);
        $message = sprintf(self::USER_DEPROVISION_FORMAT, $count, $service).PHP_EOL;

        $errorMessages = $collection->getErrorMessages();
        $errorMessageList = '';
        if (!empty($errorMessages)) {
            $errorMessageList .= sprintf(self::USER_DEPROVISION_ERROR_FORMAT).PHP_EOL.PHP_EOL;

            foreach ($errorMessages as $serviceName => $errorMessage) {
                $errorMessageList .= ' * '.$serviceName .': '.$errorMessage.PHP_EOL;
            }
        }

        return $message.$errorMessageList.PHP_EOL.self::USER_DEPROVISION_JSON_HEADING.PHP_EOL;
    }

    public function summarizeBatchResponse(
        BatchInformationResponseCollectionInterface $collection,
    ): string {

        $this->progressReporter->printDeprovisionStatistics() . PHP_EOL;

        $errorMessageList = '';

        $errorMessages = $collection->getErrorMessages();
        if (!empty($errorMessages)) {
            $errorMessageList .= sprintf(self::BATCH_DEPROVISION_ERROR_FORMAT, count($errorMessages)).PHP_EOL.PHP_EOL;
            foreach ($errorMessages as $errorMessage) {
                $errorMessageList .= ' * '.$errorMessage.PHP_EOL;
            }
        }

        return PHP_EOL.$errorMessageList.PHP_EOL.self::USER_DEPROVISION_JSON_HEADING.PHP_EOL;
    }

    private function pluralizeService(
        int $count,
    ): string {
        if ($count === 1) {
            return 'service';
        }
        return 'services';
    }
}
