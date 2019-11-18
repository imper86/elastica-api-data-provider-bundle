<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 18.11.2019
 * Time: 10:23
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter;

use Elastica\Query;
use Imper86\ElasticaApiDataProviderBundle\DataProvider\Util\PropertyMetadata;
use Symfony\Component\PropertyInfo\Type;

class BooleanFilter extends AbstractFilter
{
    public function apply(Query $query, string $resourceClass, $operationName, $context)
    {
        /** @var Query\BoolQuery $boolQuery */
        $boolQuery = $query->getQuery();

        foreach ($context['filters'] ?? [] as $property => $value) {
            $meta = $this->getMetadata($resourceClass, $property);

            if (!$meta->getType() || !$this->isValidType($meta) || !$this->isValidValue($value)) {
                continue;
            }

            $filter = new Query\Term([$property => filter_var($value, FILTER_VALIDATE_BOOLEAN)]);
            $boolQuery->addFilter($filter);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->getProperties($resourceClass) as $property) {
            if (!$this->isValidType($this->getMetadata($resourceClass, $property))) {
                continue;
            }

            $description[$property] = [
                'property' => $property,
                'type' => 'bool',
                'required' => false,
            ];
        }

        return $description;
    }

    private function isValidType(PropertyMetadata $metadata): bool
    {
        return $metadata->getType() && $metadata->getType()->getBuiltinType() === Type::BUILTIN_TYPE_BOOL;
    }

    private function isValidValue($value): bool
    {
        return in_array($value, ['true', 'false', '0', '1'], true);
    }
}
