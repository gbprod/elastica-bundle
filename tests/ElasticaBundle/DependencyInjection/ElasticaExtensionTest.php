<?php

namespace Tests\GBProd\ElasticaBundle\DependencyInjection;

use GBProd\ElasticaBundle\DependencyInjection\ElasticaExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Tests for ElasticaExtension
 *
 * @author gbprod <contact@gb-prod.fr>
 */
class ElasticaExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $extension;

    private $container;

    protected function setUp()
    {
        $this->extension = new ElasticaExtension();
        $this->container = new ContainerBuilder();
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
        $this->assertEquals(\Elastica\Client::class, $clientDefinition->getClass());
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
}
