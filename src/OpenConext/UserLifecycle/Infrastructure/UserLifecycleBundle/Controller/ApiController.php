<?php

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\Controller;

use OpenConext\UserLifecycle\Application\Api\DeprovisionApiFeatureToggle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller
{
    /**
     * @var DeprovisionApiFeatureToggle
     */
    private $enabled;

    /**
     * @param DeprovisionApiFeatureToggle $isEnabled
     */
    public function __construct(DeprovisionApiFeatureToggle $isEnabled)
    {
        $this->enabled = $isEnabled;
        die(var_dump($isEnabled));
    }

    public function deprovisionAction()
    {
        return $this->json([]);
    }
}
