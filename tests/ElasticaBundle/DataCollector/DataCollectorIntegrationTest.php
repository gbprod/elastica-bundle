<?php

namespace Tests\GBProd\ElasticaBundle\DataCollector;

use GBProd\ElasticaBundle\DataCollector\ElasticaDataCollector;
use Tests\GBProd\KernelTestCase;

/**
 * Integration tests for DataCollector
 *
 * @author FlyingDR
 */
class DataCollectorIntegrationTest extends KernelTestCase
{
    public function testDataCollectorIsRegisteredInProfiler()
    {
        $container = self::$kernel->getContainer();
        $profiler = $container->get('profiler');

        $this->assertTrue($profiler->has('elastica'));
        $this->assertInstanceOf(ElasticaDataCollector::class, $profiler->get('elastica'));
    }
}
