<?php
namespace OpenConext\UserLifecycle\Domain\Repository;

use OpenConext\UserLifecycle\Domain\Entity\LastLogin;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;

interface LastLoginRepositoryInterface
{
    /**
     * Finds the last login entry for a given person.
     *
     * If the entry does not exist, null is returned.
     *
     * @param CollabPersonId $collabPersonId
     * @return LastLogin|null
     */
    public function findLastLoginFor(CollabPersonId $collabPersonId);
}