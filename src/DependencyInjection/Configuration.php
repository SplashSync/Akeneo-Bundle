<?php

namespace Splash\Akeneo\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('splash_akeneo');

        $rootNode
            ->children()
                
                //====================================================================//
                // COMMON Parameters
                //====================================================================//
                ->scalarNode('language')
                    ->cannotBeEmpty()       
                    ->defaultValue("en_US")
                    ->info('Default Language for Products Outputs.')
                ->end()   
                ->scalarNode('currency')
                    ->cannotBeEmpty()       
                    ->defaultValue("EUR")
                    ->info('Default Channel for Products Outputs.')
                ->end()   
                ->scalarNode('scope')
                    ->cannotBeEmpty()       
                    ->defaultValue("ecommerce")
                    ->info('Default Channel for Products Outputs.')
                ->end()   
                
                //====================================================================//
                // COMMON Parameters
                //====================================================================//

                ->arrayNode('products')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('itemtype')->cannotBeEmpty()->end()
                            ->scalarNode('itemprop')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
                
            ->end()
        ;
        
        return $treeBuilder;
    }
}