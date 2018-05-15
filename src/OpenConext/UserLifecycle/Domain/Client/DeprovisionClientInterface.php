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

namespace OpenConext\UserLifecycle\Domain\Client;

use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;

interface DeprovisionClientInterface
{
    /**
     * Can be used to deprovision a user from the OpenConext platform.
     *
     * @param CollabPersonId $user
     * @param bool $dryRun
     */
    public function deprovision(CollabPersonId $user, $dryRun = false);

    /**
     * Can be used to gather information about a user on the OpenConext platform.
     *
     * Returns a Json encoded string containing the user information provided by the different deprovision API's.
     *
     * @param CollabPersonId|null $user
     * @return string
     */
    public function information(CollabPersonId $user);

    /**
     * Returns the name of the client, as configured in the parameters.yml
     *
     * @return string
     */
    public function getName();
}
