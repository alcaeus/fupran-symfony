<?php

namespace MongoDB\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('mongodb');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('clients')
                    ->useAttributeAsKey('id')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('uri')->end()
                            ->arrayNode('uriOptions')
                                ->scalarPrototype()->end()
                            ->end()
                            ->arrayNode('driverOptions')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
