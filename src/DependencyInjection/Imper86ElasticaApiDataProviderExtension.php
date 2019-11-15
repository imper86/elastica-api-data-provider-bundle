<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 14.11.2019
 * Time: 19:27
 */

namespace Imper86\ElasticaApiDataProviderBundle\DependencyInjection;

use Imper86\ElasticaApiDataProviderBundle\DataProvider\ElasticaCollectionDataProvider;
use Imper86\ElasticaApiDataProviderBundle\EventListener\QuerySearchResolveListener;
use Imper86\ElasticaApiDataProviderBundle\Filter\QueryStringFilter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class Imper86ElasticaApiDataProviderExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition(ElasticaCollectionDataProvider::class)
            ->setArgument(0, $config);

        if ($config['query_string_filter']['enabled'] ?? false) {
            $loader->load('query_string_filter.xml');

            $container->getDefinition(QuerySearchResolveListener::class)
                ->setArgument(0, $config);

            $container->getDefinition(QueryStringFilter::class)
                ->setArgument(0, $config);
        }
    }
}
