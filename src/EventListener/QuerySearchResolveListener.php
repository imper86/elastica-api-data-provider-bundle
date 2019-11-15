<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 14.11.2019
 * Time: 18:51
 */

namespace Imper86\ElasticaApiDataProviderBundle\EventListener;

use Elastica\Query\BoolQuery;
use Elastica\Query\QueryString;
use Imper86\ElasticaApiDataProviderBundle\Event\QueryEvent;

class QuerySearchResolveListener
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function onQuery(QueryEvent $event)
    {
        $defaultOperator = strtoupper($this->config['query_string_filter']['default_operator']);
        $paramName = $this->config['query_string_filter']['parameter_name'];

        if (isset($event->getFilters()[$paramName])) {
            $queryString = new QueryString($event->getFilters()[$paramName]);
            $queryString->setDefaultOperator($defaultOperator);

            /** @var BoolQuery $boolQuery */
            $boolQuery = $event->getQuery()->getQuery();
            $boolQuery->addMust($queryString);

            $event->markFilterAsResolved($paramName);
        }
    }
}
