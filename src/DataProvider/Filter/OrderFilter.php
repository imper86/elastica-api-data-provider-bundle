<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 15.11.2019
 * Time: 21:33
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter;

use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Elastica\Query;
use Symfony\Component\PropertyInfo\Type;

class OrderFilter extends AbstractFilter
{
    /**
     * @var string
     */
    private $orderParameterName;

    public function __construct(
        PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory,
        PropertyMetadataFactoryInterface $propertyMetadataFactory,
        ResourceClassResolverInterface $resourceClassResolver,
        string $orderParameterName = 'order',
        ?array $properties = null
    )
    {
        parent::__construct($propertyNameCollectionFactory, $propertyMetadataFactory, $resourceClassResolver, $properties);
        $this->orderParameterName = $orderParameterName;
    }

    public function apply(Query $query, string $resourceClass, $operationName, $context)
    {
        if (!is_array($properties = $context['filters'][$this->orderParameterName] ?? [])) {
            return;
        }

        foreach ($properties as $property => $direction) {
            if (!$this->getMetadata($resourceClass, $property)->isSimpleType()) {
                continue;
            }

            if (empty($direction) && null !== $defaultDirection = $this->properties[$property] ?? null) {
                $direction = $defaultDirection;
            }

            if (!in_array($direction = strtolower($direction), ['asc', 'desc'], true)) {
                continue;
            }

            $query->addSort([$property => $direction]);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->getProperties($resourceClass) as $property) {
            if (!$this->getMetadata($resourceClass, $property)->isSimpleType()) {
                continue;
            }

            $description["{$this->orderParameterName}[{$property}]"] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
                'schema' => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc'],
                ],
                'swagger' => [
                    'type' => 'enum',
                    'enum' => ['asc', 'desc'],
                ]
            ];
        }

        return $description;
    }

    protected function isSortable(string $resourceClass, $property): bool
    {
        $meta = $this->getMetadata($resourceClass, $property);

        if (!$meta->getType()) {
            return false;
        }

        $sortableTypes = [
            Type::BUILTIN_TYPE_BOOL,
            Type::BUILTIN_TYPE_FLOAT,
            Type::BUILTIN_TYPE_INT,
            Type::BUILTIN_TYPE_STRING,
        ];

        return in_array($meta->getType()->getBuiltinType(), $sortableTypes);
    }
}
