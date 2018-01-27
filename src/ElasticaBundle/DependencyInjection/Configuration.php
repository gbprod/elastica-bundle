<?php

namespace GBProd\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configuration
 *
 * @author gbprod <contact@gb-prod.fr>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('elastica_bundle');

        $rootNode
            ->children()
                ->scalarNode('logger')
                    ->defaultValue('logger')
                ->end()
                ->arrayNode('clients')
                    ->useAttributeAsKey('id')
                    ->defaultValue([])
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifTrue(function($values) {
                                return is_array($values) && !array_key_exists('connections', $values);
                            })
                            ->then(function($values) {
                                return ['connections' => [$values]];
                            })
                        ->end()
                        ->children()
                            ->arrayNode('connections')
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('host')->defaultValue('127.0.0.1')->end()
                                        ->scalarNode('port')->defaultValue(9200)->end()
                                        ->scalarNode('path')->defaultNull()->end()
                                        ->scalarNode('url')->defaultNull()->end()
                                        ->scalarNode('proxy')->defaultNull()->end()
                                        ->scalarNode('transport')->defaultNull()->end()
                                        ->scalarNode('persistent')->defaultTrue()->end()
                                        ->scalarNode('timeout')->defaultNull()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        if (class_exists(ContainerBuilder::class)) {
            $reflection = new \ReflectionClass(ContainerBuilder::class);
            if ($reflection->hasMethod('autowire')) {
                $rootNode
                    ->children()
                        ->scalarNode('default_client')->defaultNull()->end()
                    ->end();
            }
        }

        return $treeBuilder;
    }
}
