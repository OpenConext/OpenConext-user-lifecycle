<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace OpenConext\UserLifecycle\Tests\Integration\DataFixtures\ORM;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use OpenConext\UserLifecycle\Domain\Entity\LastLogin;
use OpenConext\UserLifecycle\Domain\ValueObject\CollabPersonId;

class DatabaseTestFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $lastLoginJames = new LastLogin(
            new CollabPersonId('urn:collab:org:surf.nl:james_watson'),
            new DateTime('2015-01-01')
        );

        $lastLoginJason = new LastLogin(
            new CollabPersonId('urn:collab:org:surf.nl:jason_mraz'),
            new DateTime('2017-05-25')
        );

        $lastLoginJimi = new LastLogin(
            new CollabPersonId('urn:collab:org:surf.nl:jimi_hendrix'),
            new DateTime('1970-09-28')
        );

        $lastLoginJohn = new LastLogin(
            new CollabPersonId('urn:collab:org:surf.nl:john_doe'),
            new DateTime('2018-01-01')
        );

        $manager->persist($lastLoginJames);
        $manager->persist($lastLoginJason);
        $manager->persist($lastLoginJimi);
        $manager->persist($lastLoginJohn);

        $manager->flush();
    }

}
