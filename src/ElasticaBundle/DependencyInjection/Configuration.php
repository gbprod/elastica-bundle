<?php

namespace GBProd\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 *
 * @author gbprod <contact@gb-prod.fr>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     * @throws \RuntimeException
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
                ->booleanNode('autowire')->defaultTrue()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
