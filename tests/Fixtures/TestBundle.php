<?php

namespace Tests\GBProd\Fixtures;

use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        // Disable logger to avoid pollution of stderr
        $container->setDefinition('logger', new Definition(NullLogger::class));
    }
}
