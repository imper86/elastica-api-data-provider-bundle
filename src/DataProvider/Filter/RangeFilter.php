<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 16.11.2019
 * Time: 16:39
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter;

use Elastica\Query;

class RangeFilter extends AbstractFilter
{
    const KEYS = [
        'gt',
        'gte',
        'lt',
        'lte',
    ];

    public function apply(Query $query, string $resourceClass, $operationName, $context)
    {
        /** @var Query\BoolQuery $boolQuery */
        $boolQuery = $query->getQuery();

        foreach ($context['filters'] ?? [] as $property => $values) {
            if (!$this->isProperFilter($resourceClass, $property, $values)) {
                continue;
            }

            $args = [];

            foreach ($values as $key => $value) {
                if (in_array($key, self::KEYS)) {
                    $args[$key] = $value;
                }
            }

            if (empty($args)) {
                continue;
            }

            $filter = new Query\Range($property, $args);
            $boolQuery->addFilter($filter);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->getProperties($resourceClass) as $property) {
            $meta = $this->getMetadata($resourceClass, $property);

            if (!$meta->getType() || !$meta->isSimpleType()) {
                continue;
            }

            foreach (self::KEYS as $operator) {
                $description["{$property}[{$operator}]"] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                ];
            }
        }

        return $description;
    }

    private function isProperFilter(string $resourceClass, string $property, $values): bool
    {
        $metadata = $this->getMetadata($resourceClass, $property);

        if (!$metadata->getType() || !is_array($values)) {
            return false;
        }

        return true;
    }
}
