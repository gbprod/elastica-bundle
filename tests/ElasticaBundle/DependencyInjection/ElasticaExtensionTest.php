<?php

namespace Tests\GBProd\ElasticaBundle\DependencyInjection;

use Elastica\Client;
use GBProd\ElasticaBundle\DataCollector\ElasticaDataCollector;
use GBProd\ElasticaBundle\DependencyInjection\ElasticaExtension;
use GBProd\ElasticaBundle\Logger\ElasticaLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Tests for ElasticaExtension
 *
 * @author gbprod <contact@gb-prod.fr>
 */
class ElasticaExtensionTest extends TestCase
{
    private $extension;

    private $container;

    protected function setUp()
    {
        $this->extension = new ElasticaExtension();
        $this->container = new ContainerBuilder();

        $this->container->setParameter('kernel.debug', true);
    }

    public function testCreateClients()
    {
        $config = [
            [
                'clients' => [
                    'default' => [
                        'host' => '127.0.0.1',
                        'port' => '9200',
                    ]
                ]
            ]
        ];

        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->has('elastica.default_client'));

        $clientDefinition = $this->container->getDefinition('elastica.default_client');
        $this->assertEquals(Client::class, $clientDefinition->getClass());
        $argument = $clientDefinition->getArgument(0);
        $this->assertEquals('127.0.0.1', $argument['connections'][0]['host']);
        $this->assertEquals('9200', $argument['connections'][0]['port']);
    }

    public function testCreateManyClients()
    {
        $config = [
            [
                'clients' => [
                    'default' => [
                        'host' => '127.0.0.1',
                        'port' => '9200',
                    ],
                    'my_client' => [
                        'host' => '192.168.0.100',
                        'port' => '9201',
                    ]
                ]
            ]
        ];

        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->has('elastica.default_client'));
        $this->assertTrue($this->container->has('elastica.my_client_client'));
    }

    public function testLoadDataCollector()
    {
        $this->container->registerExtension($this->extension);
        $this->container->loadFromExtension($this->extension->getAlias());
        $this->container->compile();

        $this->assertTrue($this->container->has('elastica.data_collector'));

        $this->assertInstanceOf(
            ElasticaDataCollector::class,
            $this->container->get('elastica.data_collector')
        );
    }

    public function testLoadDefaultLogger()
    {
        $this->extension->load([], $this->container);

        $this->assertTrue($this->container->has('elastica.logger'));

        $loggerDefinition = $this->container->getDefinition('elastica.logger');

        $this->assertEquals(ElasticaLogger::class, $loggerDefinition->getClass());
        $this->assertTrue($loggerDefinition->hasTag('monolog.logger'));
    }

    public function testLoadOverridedLogger()
    {
        $this->extension->load([['logger' => 'logger_id']], $this->container);

        $this->assertTrue($this->container->has('elastica.logger'));

        $loggerDefinition = $this->container->getDefinition('elastica.logger');

        $this->assertEquals(ElasticaLogger::class, $loggerDefinition->getClass());
        $this->assertInstanceOf(Reference::class, $loggerDefinition->getArgument(0));
        $this->assertEquals('logger_id', $loggerDefinition->getArgument(0)->__toString());
        $this->assertFalse($loggerDefinition->hasTag('monolog.logger'));
    }

    public function testLoadWithoutLogger()
    {
        $this->extension->load([['logger' => null]], $this->container);

        $this->assertTrue($this->container->has('elastica.logger'));

        $loggerDefinition = $this->container->getDefinition('elastica.logger');

        $this->assertEquals(ElasticaLogger::class, $loggerDefinition->getClass());
        $this->assertNull($loggerDefinition->getArgument(0));
    }
}
