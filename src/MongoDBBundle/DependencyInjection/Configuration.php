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
                                ->variablePrototype()->end()
                            ->end()
                            ->arrayNode('driverOptions')
                                ->variablePrototype()->end()
                            ->end()

                            ->arrayNode('databases')
                                ->useAttributeAsKey('id')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->arrayNode('options')
                                            ->variablePrototype()->end()
                                        ->end()

                                        ->arrayNode('collections')
                                            ->useAttributeAsKey('name')
                                            ->arrayPrototype()
                                                ->beforeNormalization()
                                                    ->ifString()
                                                    ->then(fn (string $name) => ['name' => $name])
                                                ->end()
                                                ->children()
                                                    ->arrayNode('options')
                                                        ->variablePrototype()->end()
                                                    ->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
