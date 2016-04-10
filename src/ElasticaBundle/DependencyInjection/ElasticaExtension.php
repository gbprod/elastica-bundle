<?php

namespace GBProd\ElasticaBundle\DependencyInjection;

use Elastica\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
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

        $this->loadClients($config, $container);
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