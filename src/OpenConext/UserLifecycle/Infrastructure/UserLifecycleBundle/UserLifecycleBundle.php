<?php

namespace OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle;

use OpenConext\UserLifecycle\Infrastructure\UserLifecycleBundle\DependencyInjection\Compiler\DeprovisionClientCollectionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class UserLifecycleBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DeprovisionClientCollectionPass());
    }
}
