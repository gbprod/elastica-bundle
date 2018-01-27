<?php

namespace Tests\GBProd\ElasticaBundle\DependencyInjection;

use Elastica\Client;
use GBProd\ElasticaBundle\DataCollector\ElasticaDataCollector;
use GBProd\ElasticaBundle\DependencyInjection\ElasticaExtension;
use GBProd\ElasticaBundle\Logger\ElasticaLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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

    private function ensureAutowiring()
    {
        $autowire = false;
        if (class_exists(ContainerBuilder::class)) {
            $reflection = new \ReflectionClass(ContainerBuilder::class);
            if ($reflection->hasMethod('autowire')) {
                $autowire = true;
            }
        }
        if (!$autowire) {
            $this->markTestSkipped('This test requires services auto-wiring functionality in Symfony 3.3+');
        }
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

    /**
     * @dataProvider dpDefaultClientDefinition
     * @param array $config
     * @param string $actualId
     * @throws \Exception
     */
    public function testSettingDefaultClientResultsIntoProperServiceAliasing($config, $actualId)
    {
        $this->ensureAutowiring();
        $this->extension->load($config, $this->container);
        $this->assertTrue($this->container->has(Client::class));
        $this->assertInstanceOf(Client::class, $this->container->get(Client::class));
        $this->assertSame($this->container->get(Client::class), $this->container->get($actualId));
    }

    public function dpDefaultClientDefinition()
    {
        return [
            // Explicit selection of default client
            [
                [
                    [
                        'clients'        => [
                            'default' => [
                                'host' => '127.0.0.1',
                                'port' => '9200',
                            ],
                            'another' => [
                                'host' => '192.168.1.1',
                                'port' => '9200',
                            ],
                        ],
                        'default_client' => 'default'
                    ],
                ],
                'elastica.default_client',
            ],
            // Make sure that we're not selecting first client in a case of explicit selection
            [
                [
                    [
                        'clients'        => [
                            'default' => [
                                'host' => '127.0.0.1',
                                'port' => '9200',
                            ],
                            'another' => [
                                'host' => '192.168.1.1',
                                'port' => '9200',
                            ],
                        ],
                        'default_client' => 'another'
                    ],
                ],
                'elastica.another_client',
            ],
            // Default selection should select first client from the list
            [
                [
                    [
                        'clients'        => [
                            'default' => [
                                'host' => '127.0.0.1',
                                'port' => '9200',
                            ],
                            'another' => [
                                'host' => '192.168.1.1',
                                'port' => '9200',
                            ],
                        ],
                        'default_client' => null
                    ],
                ],
                'elastica.default_client',
            ],
        ];
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function testSettingIncorrectDefaultClientResultsIntoException()
    {
        $this->ensureAutowiring();
        $config = [
            [
                'clients'        => [
                    'default' => [
                        'host' => '127.0.0.1',
                        'port' => '9200',
                    ]
                ],
                'default_client' => 'incorrect'
            ]
        ];

        $this->extension->load($config, $this->container);
    }

    public function testDisablingDefaultClientShouldNotResultIntoItsRegistration()
    {
        $this->ensureAutowiring();
        $config = [
            [
                'clients'        => [
                    'default' => [
                        'host' => '127.0.0.1',
                        'port' => '9200',
                    ]
                ],
                'default_client' => false
            ]
        ];

        $this->extension->load($config, $this->container);
        $this->assertFalse($this->container->has(Client::class));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\LogicException
     */
    public function testAttemptToRedefineAlreadyAvailableServiceShouldResultInException()
    {
        $this->ensureAutowiring();
        $this->container->setDefinition(Client::class, new Definition(Client::class));
        $config = [
            [
                'clients' => [
                    'default' => [
                        'host' => '127.0.0.1',
                        'port' => '9200',
                    ]
                ],
            ]
        ];

        $this->extension->load($config, $this->container);
    }
}
