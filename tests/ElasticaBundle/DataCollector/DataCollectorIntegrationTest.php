<?php

namespace Tests\GBProd\ElasticaBundle\DataCollector;

use GBProd\ElasticaBundle\DataCollector\ElasticaDataCollector;
use Tests\GBProd\KernelTestCase;

class DataCollectorIntegrationTest extends KernelTestCase
{
    public function testDataCollectorIsRegisteredInContainer()
    {
        $container = self::$kernel->getContainer();
        $this->assertTrue($container->has('elastica.data_collector'));
        $service = $container->get('elastica.data_collector');
        $this->assertInstanceOf(ElasticaDataCollector::class, $service);
    }

    public function testDataCollectorIsRegisteredInProfiler()
    {
        $container = self::$kernel->getContainer();
        $profiler = $container->get('profiler');
        $this->assertTrue($profiler->has('elastica'));
        $this->assertInstanceOf(ElasticaDataCollector::class, $profiler->get('elastica'));
    }
}
