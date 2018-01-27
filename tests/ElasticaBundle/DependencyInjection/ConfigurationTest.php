<?php

namespace Tests\GBProd\ElasticaBundle\DependencyInjection;

use GBProd\ElasticaBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Tests for Configuration
 *
 * @author gbprod <contact@gb-prod.fr>
 */
class ConfigurationTest extends TestCase
{
    private $configuration;

    public function setUp()
    {
        $this->configuration = new Configuration();
    }

    /**
     * @return bool
     */
    private function haveAutowiring()
    {
        if (!class_exists(ContainerBuilder::class)) {
            return false;
        }
        $reflection = new \ReflectionClass(ContainerBuilder::class);
        if ($reflection->hasMethod('autowire')) {
            return true;
        }
        return false;
    }

    public function testEmptyConfiguration()
    {
        $processed = $this->process([]);

        $expected = [
            'clients' => [],
            'logger'  => 'logger',
        ];
        if ($this->haveAutowiring()) {
            $expected['default_client'] = null;
        }
        $this->assertEquals($expected, $processed);
    }

    protected function process(array $config)
    {
        $processor = new Processor();

        return $processor->processConfiguration(
            $this->configuration,
            $config
        );
    }

    public function testProcessOneClient()
    {
        $processed = $this->process([
            [
                'clients' => [
                    'default' => [
                        'host' => '127.0.0.1',
                        'port' => '9200',
                    ]
                ]
            ]
        ]);

        $this->assertArrayHasKey('connections', $processed['clients']['default']);
        $this->assertEquals(
            '127.0.0.1',
            $processed['clients']['default']['connections'][0]['host']
        );
        $this->assertEquals(
            '9200',
            $processed['clients']['default']['connections'][0]['port']
        );
    }

    public function testProcessTwoClients()
    {
        $processed = $this->process([
            [
                'clients' => [
                    'default'   => [
                        'host' => '127.0.0.1',
                        'port' => '9200',
                    ],
                    'my_client' => [
                        'host' => '192.168.0.100',
                        'port' => '9201',
                    ]
                ]
            ]
        ]);

        $this->assertArrayHasKey('connections', $processed['clients']['default']);
        $this->assertArrayHasKey('connections', $processed['clients']['my_client']);
    }

    public function testProcessManyConnections()
    {
        $processed = $this->process([
            [
                'clients' => [
                    'default' => [
                        'connections' => [
                            [
                                'host' => '127.0.0.1',
                                'port' => '9200',
                            ],
                            [
                                'host' => '192.168.0.100',
                                'port' => '9201',
                            ],
                        ]
                    ],
                ]
            ]
        ]);


        $this->assertArrayHasKey('connections', $processed['clients']['default']);
        $connections = $processed['clients']['default']['connections'];
        $this->assertEquals('127.0.0.1', $connections[0]['host']);
        $this->assertEquals('9200', $connections[0]['port']);
        $this->assertEquals('192.168.0.100', $connections[1]['host']);
        $this->assertEquals('9201', $connections[1]['port']);
    }

    public function testProcessAddDefaultValuesForConnections()
    {
        $processed = $this->process([
            [
                'clients' => [
                    'default' => [
                        'connections' => [
                            [
                                'host' => '127.0.0.1',
                                'port' => '9200',
                            ]
                        ]
                    ],
                ]
            ]
        ]);

        $this->assertArrayHasKey('connections', $processed['clients']['default']);
        $this->assertNull($processed['clients']['default']['connections'][0]['path']);
        $this->assertNull($processed['clients']['default']['connections'][0]['url']);
        $this->assertNull($processed['clients']['default']['connections'][0]['proxy']);
        $this->assertNull($processed['clients']['default']['connections'][0]['transport']);
        $this->assertNull($processed['clients']['default']['connections'][0]['timeout']);
        $this->assertTrue($processed['clients']['default']['connections'][0]['persistent']);
    }

    public function testDefaultClientConfigurationOptionIsAvailableInACaseOfAutowiringSupport()
    {
        $processed = $this->process([
            [
                'clients' => [
                    'default' => [
                        'host' => '127.0.0.1',
                        'port' => '9200',
                    ]
                ],
            ]
        ]);
        if ($this->haveAutowiring()) {
            $this->assertArrayHasKey('default_client', $processed);
        } else {
            $this->assertArrayNotHasKey('default_client', $processed);
        }
    }
}
