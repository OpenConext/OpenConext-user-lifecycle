<?php
namespace OpenConext\UserLifecycle\Domain\Query;

use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;

class LastLoginByUserIdQuery
{
    /**
     * @var CollabPersonId
     */
    private $personId;

    public function __construct(CollabPersonId $userId)
    {
        $this->personId = $userId;
    }

    public function getPersonId()
    {
        return $this->personId;
    }
}
