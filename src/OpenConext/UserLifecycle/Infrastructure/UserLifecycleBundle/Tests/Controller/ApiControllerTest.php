<?php

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function test_user_information_can_be_retrieved()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'api/deprovision');

        $this->assertEquals('[]', $client->getResponse()->getContent());
    }

}
