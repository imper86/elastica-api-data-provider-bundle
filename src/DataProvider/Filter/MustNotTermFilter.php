<?php


namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter;


use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Elastica\Query;

class MustNotTermFilter extends AbstractFilter
{
    /**
     * @var string
     */
    private $parameterName;

    public function __construct(
        PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory,
        PropertyMetadataFactoryInterface $propertyMetadataFactory,
        ResourceClassResolverInterface $resourceClassResolver,
        string $parameterName = 'not',
        ?array $properties = null
    )
    {
        parent::__construct(
            $propertyNameCollectionFactory,
            $propertyMetadataFactory,
            $resourceClassResolver,
            $properties
        );
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
            $metadata = $this->getMetadata($resourceClass, $property);

            if (!$metadata->isSimpleType() && !$metadata->isEnum()) {
                continue;
            }

            $filter = is_array($value) ? new Query\Terms($property, $value) : new Query\Term([$property => $value]);
            $boolQuery->addMustNot($filter);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->getProperties($resourceClass) as $property) {
            $metadata = $this->getMetadata($resourceClass, $property);

            if (!$metadata->isSimpleType() && !$metadata->isEnum()) {
                continue;
            }

            if ($metadata->isEnum()) {
                $values = array_values(call_user_func($metadata->getType()->getClassName() . '::toArray'));

                $descriptionModel = function (bool $isCollection) use ($property, $values): array {
                    return [
                        'property' => $property,
                        'type' => 'string',
                        'required' => false,
                        'schema' => [
                            'type' => $isCollection ? 'array[string]' : 'string',
                            'enum' => $values,
                        ],
                        'swagger' => [
                            'type' => 'enum',
                            'enum' => $values,
                        ],
                        'is_collection' => $isCollection
                    ];
                };
            } else {
                $descriptionModel = function (bool $isCollection) use ($property): array {
                    return [
                        'property' => $property,
                        'type' => 'string',
                        'required' => false,
                        'is_collection' => $isCollection,
                    ];
                };
            }

            $description["{$this->parameterName}[{$property}]"] = $descriptionModel(false);
            $description["{$this->parameterName}[{$property}][]"] = $descriptionModel(true);
        }

        return $description;
    }

}
