<?php

namespace Tpg\ExtjsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    protected $bundles;

    public function __construct($bundles) {
        $this->bundles = $bundles;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tpg_extjs');

        $rootNode
            ->children()
                ->arrayNode('remoting')
                    ->children()
                        ->arrayNode('bundles')
                            ->prototype('scalar')
                                ->validate()
                                    ->ifNotInArray($this->bundles)
                                    ->thenInvalid('%s is not a valid bundle.')
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
