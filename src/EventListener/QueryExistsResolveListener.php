<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 15.11.2019
 * Time: 18:14
 */

namespace Imper86\ElasticaApiDataProviderBundle\EventListener;

use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Imper86\ElasticaApiDataProviderBundle\Event\QueryEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class QueryExistsResolveListener
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function onQuery(QueryEvent $event)
    {
        $paramName = $this->parameterBag->get('api_platform.collection.exists_parameter_name');
        $filtersRef = &$event->getFilters()[$paramName];

        if (isset($filtersRef) && is_array($filtersRef)) {
            /** @var BoolQuery $boolQuery */
            $boolQuery = $event->getQuery()->getQuery();

            foreach ($filtersRef as $fieldName => $value) {
                $query = new Exists($fieldName);

                switch ($value) {
                    case 'true':
                    case '1':
                        $boolQuery->addFilter($query);
                        break;
                    case 'false':
                    case '0':
                        $boolQuery->addMustNot($query);
                        break;
                }
            }

            $event->markFilterAsResolved($paramName);
        }
    }
}
