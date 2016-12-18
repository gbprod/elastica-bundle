<?php

namespace GBProd\ElasticaBundle\DependencyInjection;

use Elastica\Client;
use GBProd\ElasticaBundle\Logger\ElasticaLogger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');

        $this->loadLogger($config, $container);
        $this->loadClients($config, $container);
    }

    private function loadLogger(array $config, ContainerBuilder $container)
    {
        $definition = $container
            ->register('elastica.logger', ElasticaLogger::class)
            ->addArgument($this->createLoggerReference($config))
            ->addArgument('%kernel.debug%')
        ;

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

    private function loadClients(array $config, ContainerBuilder $container)
    {
        foreach ($config['clients'] as $clientName => $clientConfig) {
            $this->loadClient($clientName, $clientConfig, $container);
        }
    }

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
        ;
    }

    private function createClientId($clientName)
    {
        return sprintf(
            self::CLIENT_ID_TEMPLATE,
            $clientName
        );
    }
}
