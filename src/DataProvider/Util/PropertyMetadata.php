<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 16.11.2019
 * Time: 15:19
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Util;

use MyCLabs\Enum\Enum;
use Symfony\Component\PropertyInfo\Type;

class PropertyMetadata
{
    /**
     * @var Type|null
     */
    private $type;
    /**
     * @var bool|null
     */
    private $hasAssociation;
    /**
     * @var string|null
     */
    private $resourceClass;
    /**
     * @var string|null
     */
    private $property;

    public function __construct(
        ?Type $type = null,
        ?bool $hasAssociation = null,
        ?string $resourceClass = null,
        ?string $property = null
    )
    {
        $this->type = $type;
        $this->hasAssociation = $hasAssociation;
        $this->resourceClass = $resourceClass;
        $this->property = $property;
    }

    /**
     * @return Type|null
     */
    public function getType(): ?Type
    {
        return $this->type;
    }

    /**
     * @return bool|null
     */
    public function getHasAssociation(): ?bool
    {
        return $this->hasAssociation;
    }

    /**
     * @return string|null
     */
    public function getResourceClass(): ?string
    {
        return $this->resourceClass;
    }

    /**
     * @return string|null
     */
    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function isSimpleType(): ?bool
    {
        if (!$this->getType()) {
            return null;
        }

        $simpleTypes = [
            Type::BUILTIN_TYPE_STRING,
            Type::BUILTIN_TYPE_INT,
            Type::BUILTIN_TYPE_FLOAT,
            Type::BUILTIN_TYPE_BOOL,
            Type::BUILTIN_TYPE_ARRAY,
        ];

        return in_array($this->getType()->getBuiltinType(), $simpleTypes) ||
            (
                Type::BUILTIN_TYPE_OBJECT === $this->getType()->getBuiltinType() &&
                is_a($this->getType()->getClassName(), \DateTimeInterface::class, true)
            )
        ;
    }

    public function isEnum(): ?bool
    {
        if (!$this->getType()) {
            return null;
        }

        return $this->getType()->getBuiltinType() === Type::BUILTIN_TYPE_OBJECT &&
            is_a($this->getType()->getClassName(), Enum::class, true);
    }
}
