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
            $metadata = $this->getMetadata($resourceClass, $property);

            if (!$metadata->isSimpleType() && !$metadata->isEnum()) {
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

            $description[$property] = $descriptionModel(false);
            $description["{$property}[]"] = $descriptionModel(true);
        }

        return $description;
    }
}
