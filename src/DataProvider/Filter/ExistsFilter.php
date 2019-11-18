<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 16.11.2019
 * Time: 16:26
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter;

use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Elastica\Query;

class ExistsFilter extends AbstractFilter
{
    /**
     * @var string
     */
    private $parameterName;

    public function __construct(
        PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory,
        PropertyMetadataFactoryInterface $propertyMetadataFactory,
        ResourceClassResolverInterface $resourceClassResolver,
        string $parameterName = 'exists',
        ?array $properties = null
    )
    {
        parent::__construct($propertyNameCollectionFactory, $propertyMetadataFactory, $resourceClassResolver, $properties);
        $this->parameterName = $parameterName;
    }

    public function apply(Query $query, string $resourceClass, $operationName, $context)
    {
        if (!is_array($properties = $context['filters'][$this->parameterName] ?? [])) {
            return;
        }

        /** @var Query\BoolQuery $boolQuery */
        $boolQuery = $query->getQuery();

        foreach ($properties as $property => $value) {
            if (!$this->getMetadata($resourceClass, $property)->getType()) {
                continue;
            }

            $filter = new Query\Exists($property);

            switch ($value) {
                case '1':
                case 'true':
                    $boolQuery->addFilter($filter);
                    break;
                case '0':
                case 'false':
                    $boolQuery->addMustNot($filter);
                    break;
            }
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->getProperties($resourceClass) as $property) {
            if (!$this->getMetadata($resourceClass, $property)->getType()) {
                continue;
            }

            $description["{$this->parameterName}[{$property}]"] = [
                'property' => $property,
                'type' => 'bool',
                'required' => false,
            ];
        }

        return $description;
    }
}
