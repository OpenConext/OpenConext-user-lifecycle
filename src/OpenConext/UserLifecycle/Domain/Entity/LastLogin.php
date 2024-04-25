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

namespace OpenConext\UserLifecycle\Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;
use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Repository\LastLoginRepository;

#[ORM\Entity(repositoryClass: LastLoginRepository::class)]
class LastLogin
{
    public function __construct(
        CollabPersonId $collabPersonId,
        #[ORM\Column(name: 'lastseen', type: 'datetime')]
        private readonly DateTime $lastLoginDate,
    ) {
        $this->collabPersonId = $collabPersonId->getCollabPersonId();
    }

    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(name: 'userid', length: 150, unique: true)]
    private $collabPersonId;

    public function getCollabPersonId(): string
    {
        return $this->collabPersonId;
    }

    public function getLastLoginDate(): DateTime
    {
        return $this->lastLoginDate;
    }
}
