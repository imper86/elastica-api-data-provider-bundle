<?php


namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter;


use Elastica\Query;

class SimpleQueryStringFilter extends AbstractFilter
{
    public function apply(Query $query, string $resourceClass, $operationName, $context)
    {
        /** @var Query\BoolQuery $boolQuery */
        $boolQuery = $query->getQuery();

        foreach ($context['filters'] ?? [] as $property => $value) {
            if (!$this->getMetadata($resourceClass, $property)->isSimpleType()) {
                continue;
            }

            if ('string' !== gettype($value)) {
                continue;
            }

            $sqs = new Query\SimpleQueryString($value, [$property]);
            $sqs->setDefaultOperator('AND');

            $boolQuery->addMust($sqs);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        $description = [];

        foreach ($this->getProperties($resourceClass) as $property) {
            if (!$this->getMetadata($resourceClass, $property)->isSimpleType()) {
                continue;
            }

            $description[$property] = [
                'property' => $property,
                'type' => 'string',
                'required' => false,
            ];
        }

        return $description;
    }
}
