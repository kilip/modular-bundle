<?php

/*
 * This file is part of the ModularBundle project.
 *
 * (c) Anthonius Munthi <https://itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Bundle\Modular\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress TooFew
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('modular_extension');
            $rootNode    = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $rootNode    = $treeBuilder->root('modular_extension');
        }

        $rootNode
            ->children()
                ->booleanNode('doctrine_annotation')->defaultTrue()->end()
                ->scalarNode('module_root_dir')->defaultValue('src')->end()
                ->arrayNode('doctrine')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('use_annotation')->defaultTrue()->end()
                        ->booleanNode('use_orm')->defaultTrue()->end()
                        ->booleanNode('use_mongodb')->defaultFalse()->end()
                        ->scalarNode('entity_dir')->defaultValue('Entity')->end()
                        ->scalarNode('document_dir')->defaultValue('Document')->end()
                        ->scalarNode('mapping_type')->defaultValue('xml')->end()
                        ->scalarNode('mapping_dir')->defaultValue('Resources/doctrine')->end()
                    ->end()
                ->end()
                ->arrayNode('config_paths')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('api_platform')->defaultValue('Resources/api')->end()
                        ->scalarNode('validation')->defaultValue('Resources/validation')->end()
                        ->scalarNode('serialization')->defaultValue('Resources/serialization')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
