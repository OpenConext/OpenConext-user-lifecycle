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

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Repository;

use DateTime;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\ORM\EntityRepository;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollection;
use OpenConext\UserLifecycle\Domain\Collection\LastLoginCollectionInterface;
use OpenConext\UserLifecycle\Domain\Repository\LastLoginRepositoryInterface;
use OpenConext\UserLifecycle\Domain\ValueObject\InactivityPeriod;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Exception\DatabaseConnectionException;
use Webmozart\Assert\Assert;

class LastLoginRepository extends EntityRepository implements LastLoginRepositoryInterface
{
    private ?\DateTime $now = null;

    public function findDeprovisionCandidates(
        InactivityPeriod $inactivityPeriod,
    ): LastLoginCollectionInterface {
        $expirationDate = $this->getNow()->modify(sprintf('-%d months', $inactivityPeriod->getInactivityPeriodInMonths()));

        $qb = $this->createQueryBuilder('ll');
        try {
            $results = $qb
                ->where('ll.lastLoginDate <= :expirationDate')
                ->orderBy('ll.lastLoginDate', 'ASC')
                ->setParameter('expirationDate', $expirationDate)
                ->getQuery()
                ->getResult();
        } catch (ConnectionException) {
            throw new DatabaseConnectionException();
        }
        return LastLoginCollection::from($results);
    }

    /**
     * Delete an entry from the last login table identified by collabPersonId
     *
     * @param string $collabPersonId
     */
    public function delete(
        string $collabPersonId,
    ): void {
        Assert::stringNotEmpty($collabPersonId);

        $this->createQueryBuilder('ll')
            ->delete()
            ->where('ll.collabPersonId = :collabPersonId')
            ->setParameter('collabPersonId', $collabPersonId)
            ->getQuery()
            ->execute();
    }

    /**
     * For now only used for testing purposes but can be used in the future to
     * deprovision/retrieve info of users at a given date.
     */
    public function setNow(
        DateTime $now,
    ): void {
        $this->now = $now;
    }

    private function getNow(): DateTime
    {
        if ($this->now instanceof DateTime) {
            return $this->now;
        }
        return new DateTime();
    }
}
