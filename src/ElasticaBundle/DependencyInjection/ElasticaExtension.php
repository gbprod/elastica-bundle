<?php

namespace GBProd\ElasticaBundle\DependencyInjection;

use Elastica\Client;
use GBProd\ElasticaBundle\Logger\ElasticaLogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Extension class for ElasticaExtension
 *
 * @author gbprod <contact@gb-prod.fr>
 */
class ElasticaExtension extends Extension
{
    const CLIENT_ID_TEMPLATE = 'elastica.%s_client';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yml');

        $this->loadLogger($config, $container);
        $this->loadClients($config, $container);
        $this->setupAutowire($config, $container);
    }

    private function loadLogger(array $config, ContainerBuilder $container)
    {
        $definition = $container
            ->register('elastica.logger', ElasticaLogger::class)
            ->addArgument($this->createLoggerReference($config))
            ->addArgument('%kernel.debug%')
            ->setPublic(true);

        if ('logger' === $config['logger']) {
            $definition->addTag('monolog.logger', ['channel' => 'elastica']);
        }
    }

    private function createLoggerReference(array $config)
    {
        if (null !== $config['logger']) {
            return new Reference(
                $config['logger'],
                ContainerInterface::IGNORE_ON_INVALID_REFERENCE
            );
        }

        return null;
    }

    /**
     * @param array $config
     * @param ContainerBuilder $container
     * @throws \Symfony\Component\DependencyInjection\Exception\LogicException
     * @throws InvalidArgumentException
     */
    private function loadClients(array $config, ContainerBuilder $container)
    {
        foreach ($config['clients'] as $clientName => $clientConfig) {
            $this->loadClient($clientName, $clientConfig, $container);
        }
    }

    /**
     * @param string $clientName
     * @param array $clientConfig
     * @param ContainerBuilder $container
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    private function loadClient($clientName, array $clientConfig, ContainerBuilder $container)
    {
        $container
            ->register($this->createClientId($clientName), Client::class)
            ->addArgument($clientConfig)
            ->addMethodCall('setLogger', [
                new Reference('elastica.logger')
            ])
            ->addMethodCall('setConfigValue', [
                'log',
                $container->getParameter('kernel.debug')
            ])
            ->setPublic(true);
    }

    /**
     * Configure service auto-wiring for default Elastica client
     * for Symfony 3.3+
     *
     * @param array $config
     * @param ContainerBuilder $container
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\LogicException
     */
    private function setupAutowire(array $config, ContainerBuilder $container)
    {
        if (!method_exists($container, 'autowire')) {
            // This container have no support for services auto-wiring
            return;
        }
        if (!$config['autowire']) {
            // Auto-wiring for default client is explicitly disabled
            return;
        }
        if (!array_key_exists('default', $config['clients'])) {
            // No "default" client is available
            return;
        }
        if ($container->hasDefinition(Client::class)) {
            throw new LogicException('Default Elasticsearch client autowiring setup is enabled, but Elastica client service is already defined in container');
        }
        $container->setAlias(Client::class, $this->createClientId('default'));
    }

    private function createClientId($clientName)
    {
        return sprintf(
            self::CLIENT_ID_TEMPLATE,
            $clientName
        );
    }
}
