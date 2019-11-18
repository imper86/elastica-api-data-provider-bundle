<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 15.11.2019
 * Time: 19:39
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Extension;

use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Elastica\Query;
use Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter\FilterInterface;
use Psr\Container\ContainerInterface;

class QueryExtension implements ExtensionInterface
{
    /**
     * @var ResourceMetadataFactoryInterface
     */
    private $metadataFactory;
    /**
     * @var ContainerInterface
     */
    private $filterLocator;

    public function __construct(ResourceMetadataFactoryInterface $metadataFactory, ContainerInterface $filterLocator)
    {
        $this->metadataFactory = $metadataFactory;
        $this->filterLocator = $filterLocator;
    }

    public function applyToCollection(Query $query, string $resourceClass, string $operationName, array $context)
    {
        $resourceMetadata = $this->metadataFactory->create($resourceClass);
        $resourceFilters = $resourceMetadata->getCollectionOperationAttribute($operationName, 'filters', [], true);

        if (empty($resourceFilters)) {
            return;
        }

        foreach ($resourceFilters as $filterId) {
            $filter = $this->filterLocator->get($filterId);

            if ($filter instanceof FilterInterface) {
                $filter->apply($query, $resourceClass, $operationName, $context);
            }
        }
    }
}
