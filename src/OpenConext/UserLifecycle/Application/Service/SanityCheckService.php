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

use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use OpenConext\UserLifecycle\Domain\Exception\EmptyLastLoginCollectionException;
use OpenConext\UserLifecycle\Domain\Exception\InvalidLastLoginCollectionException;
use OpenConext\UserLifecycle\Domain\Service\SanityCheckServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SanityCheckService implements SanityCheckServiceInterface
{
    /**
     * The maximum allowed number of LastLogin entries that may be
     * deprovisioned at one time.
     */
    public function __construct(
        #[Autowire(param: 'user_quota')]
        private readonly int             $userQuota,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Performs a sanity check on the LastLoginCollection
     *
     * When certain rules are violated (max number of entries), the
     * method will throw the InvalidLastLoginCollectionException. This
     * should halt the deprovisioning run.
     *
     * @throws InvalidLastLoginCollectionException
     * @throws EmptyLastLoginCollectionException
     */
    public function check(
        LastLoginCollectionInterface $lastLoginCollection,
    ): void {
        $count = count($lastLoginCollection);

        if ($count === 0) {
            throw new EmptyLastLoginCollectionException('No candidates found for deprovisioning');
        }

        if ($count > $this->userQuota) {
            throw new  InvalidLastLoginCollectionException(
                sprintf(
                    'Too much candidates found for deprovisioning. %d exceeds the limit set at %d by %d.',
                    $count,
                    $this->userQuota,
                    ($count - $this->userQuota),
                ),
            );
        }

        $this->logger->debug('Ascertained the proposed list of deprovision candidates is valid.');
    }
}
