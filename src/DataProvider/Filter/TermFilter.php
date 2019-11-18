<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 16.11.2019
 * Time: 14:34
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter;

use Elastica\Query;

class TermFilter extends AbstractFilter
{
    public function apply(Query $query, string $resourceClass, $operationName, $context)
    {
        /** @var Query\BoolQuery $boolQuery */
        $boolQuery = $query->getQuery();

        foreach ($context['filters'] ?? [] as $property => $values) {
            if (!$this->getMetadata($resourceClass, $property)->isSimpleType()) {
                continue;
            }

            $filter = is_array($values) ? new Query\Terms($property, $values) : new Query\Term([$property => $values]);

            $boolQuery->addFilter($filter);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->getProperties($resourceClass) as $property) {
            if (!$this->getMetadata($resourceClass, $property)->isSimpleType()) {
                continue;
            }

            $description["{$property}[]"] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
            ];
        }

        return $description;
    }
}
