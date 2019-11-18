<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 15.11.2019
 * Time: 16:44
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter;


use Elastica\Query;

class QueryStringFilter implements FilterInterface
{
    private $paramName;
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->paramName = $this->config['query_string_filter']['parameter_name'] ?? 'q';
    }

    public function apply(Query $query, string $resourceClass, $operationName, $context)
    {
        if (isset($context['filters'][$this->paramName])) {
            /** @var Query\BoolQuery $boolQuery */
            $boolQuery = $query->getQuery();

            $queryString = new Query\QueryString($context['filters'][$this->paramName]);
            $queryString->setDefaultOperator($this->config['query_string_filter']['default_operator'] ?? 'OR');

            $boolQuery->addMust($queryString);
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            $this->paramName => [
                'property' => null,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Query string filter, you can use Lucene with this',
                    'name' => $this->paramName,
                    'type' => 'string',
                ]
            ],
        ];
    }
}
