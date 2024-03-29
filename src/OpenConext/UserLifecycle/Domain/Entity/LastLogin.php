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

namespace OpenConext\UserLifecycle\Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;

/**
 * @ORM\Entity(
 *     repositoryClass="OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Repository\LastLoginRepository"
 * )
 */
class LastLogin
{
    public function __construct(CollabPersonId $collabPersonId, DateTime $lastLoginDate)
    {
        $this->collabPersonId = $collabPersonId->getCollabPersonId();
        $this->lastLoginDate = $lastLoginDate;
    }

    /**
     * @var string
     * @ORM\Id()
     * @ORM\Column(length=150, unique=true, name="userid")
     */
    private $collabPersonId;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", name="lastseen")
     */
    private $lastLoginDate;

    /**
     * @return string
     */
    public function getCollabPersonId()
    {
        return $this->collabPersonId;
    }

    /**
     * @return DateTime
     */
    public function getLastLoginDate()
    {
        return $this->lastLoginDate;
    }
}
