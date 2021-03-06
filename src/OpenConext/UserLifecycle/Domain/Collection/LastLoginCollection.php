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

namespace OpenConext\UserLifecycle\Domain\Collection;

use OpenConext\UserLifecycle\Domain\Entity\LastLogin;

class LastLoginCollection implements LastLoginCollectionInterface
{
    /**
     * @var array
     */
    private $data = [];

    public static function from(array $results)
    {
        $collection = new self();
        foreach ($results as $lastLogin) {
            $collection->add($lastLogin);
        }

        return $collection;
    }

    /**
     * @param LastLogin $lastLogin
     */
    public function add(LastLogin $lastLogin)
    {
        $this->data[] = $lastLogin;
    }

    /**
     * @return LastLogin[]
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }
}
