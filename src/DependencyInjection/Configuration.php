<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 15.11.2019
 * Time: 16:17
 */

namespace Imper86\ElasticaApiDataProviderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('imper86_elastica_api_data_provider');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('imper86_elastica_api_data_provider');
        }

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('query_string_filter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('parameter_name')->defaultValue('q')->cannotBeEmpty()->end()
                        ->enumNode('default_operator')
                            ->values(['OR', 'AND'])
                            ->defaultValue('OR')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('resources')->end()
            ->end();

        return $treeBuilder;
    }
}
