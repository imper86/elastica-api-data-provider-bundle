<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 14.11.2019
 * Time: 18:48
 */

namespace Imper86\ElasticaApiDataProviderBundle\EventListener;

use Imper86\ElasticaApiDataProviderBundle\Event\QueryEvent;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class QueryOrderResolveListener
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
        $paramName = $this->parameterBag->get('api_platform.collection.order_parameter_name');

        if (isset($event->getFilters()[$paramName])) {
            foreach ($event->getFilters()[$paramName] as $field => $order) {
                $event->getQuery()->addSort([$field => $order]);
            }

            $event->markFilterAsResolved($paramName);
        }
    }
}
