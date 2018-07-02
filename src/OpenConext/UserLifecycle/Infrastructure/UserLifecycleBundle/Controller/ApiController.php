<?php

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller
{
    public function deprovisionAction()
    {
        return $this->json([]);
    }
}
