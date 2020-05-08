<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 16.11.2019
 * Time: 13:20
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter;

use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Exception\PropertyNotFoundException;
use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyNameCollectionFactoryInterface;
use Imper86\ElasticaApiDataProviderBundle\DataProvider\Util\PropertyMetadata;
use Symfony\Component\PropertyInfo\Type;

abstract class AbstractFilter implements FilterInterface
{
    /**
     * @var PropertyNameCollectionFactoryInterface
     */
    protected $propertyNameCollectionFactory;
    /**
     * @var PropertyMetadataFactoryInterface
     */
    protected $propertyMetadataFactory;
    /**
     * @var ResourceClassResolverInterface
     */
    protected $resourceClassResolver;
    /**
     * @var array|null
     */
    protected $properties;
    /**
     * @var array
     */
    private $metadatas = [];

    public function __construct(
        PropertyNameCollectionFactoryInterface $propertyNameCollectionFactory,
        PropertyMetadataFactoryInterface $propertyMetadataFactory,
        ResourceClassResolverInterface $resourceClassResolver,
        ?array $properties = null
    )
    {
        $this->propertyNameCollectionFactory = $propertyNameCollectionFactory;
        $this->propertyMetadataFactory = $propertyMetadataFactory;
        $this->resourceClassResolver = $resourceClassResolver;
        $this->properties = $properties;
    }

    protected function getProperties(string $resourceClass): \Traversable
    {
        if (null !== $this->properties) {
            return yield from array_keys($this->properties);
        }

        try {
            yield from $this->propertyNameCollectionFactory->create($resourceClass);
        } catch (ResourceClassNotFoundException $exception) {
        }
    }

    protected function hasProperty(string $resourceClass, string $property): bool
    {
        return in_array($property, iterator_to_array($this->getProperties($resourceClass)), true);
    }

    protected function getMetadata(string $resourceClass, string $property): PropertyMetadata
    {
        $ref = &$this->metadatas["{$resourceClass}::{$property}"];

        if (isset($ref)) {
            return $ref;
        }

        $noop = new PropertyMetadata();

        if (!$this->hasProperty($resourceClass, $property)) {
            $ref = $noop;

            return $noop;
        }

        $properties = explode('.', $property);
        $totalProperties = \count($properties);
        $currentResourceClass = $resourceClass;
        $hasAssociation = false;
        $currentProperty = null;
        $type = null;

        foreach ($properties as $index => $currentProperty) {
            try {
                $propertyMetadata = $this->propertyMetadataFactory->create($currentResourceClass, $currentProperty);
            } catch (PropertyNotFoundException $e) {
                $ref = $noop;
                return $noop;
            }

            if (null === $type = $propertyMetadata->getType()) {
                $ref = $noop;
                return $noop;
            }

            ++$index;
            $builtinType = $type->getBuiltinType();

            if (Type::BUILTIN_TYPE_OBJECT !== $builtinType && Type::BUILTIN_TYPE_ARRAY !== $builtinType) {
                if ($totalProperties === $index) {
                    break;
                }

                $ref = $noop;
                return $noop;
            }

//            if ($type->isCollection() && null === $type = $type->getCollectionValueType()) {
//                $ref = $noop;
//                return $noop;
//            }

            if (Type::BUILTIN_TYPE_ARRAY === $builtinType && Type::BUILTIN_TYPE_OBJECT !== $type->getBuiltinType()) {
                if ($totalProperties === $index) {
                    break;
                }

                $ref = $noop;
                return $noop;
            }

            if (null === $className = $type->getClassName()) {
                $ref = $noop;
                return $noop;
            }

            if ($isResourceClass = $this->resourceClassResolver->isResourceClass($className)) {
                $currentResourceClass = $className;
            } elseif ($totalProperties !== $index) {
                $ref = $noop;
                return $noop;
            }

            $hasAssociation = $totalProperties === $index && $isResourceClass;
        }

        $ref = new PropertyMetadata($type, $hasAssociation, $currentResourceClass, $currentProperty);

        return $ref;
    }
}
