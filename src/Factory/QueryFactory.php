<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 14.11.2019
 * Time: 18:42
 */

namespace Imper86\ElasticaApiDataProviderBundle\Factory;

use Elastica\Query;
use Imper86\ElasticaApiDataProviderBundle\Event\QueryEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class QueryFactory implements QueryFactoryInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function transform(array $context): Query
    {
        $query = new Query(new Query\BoolQuery());
        $event = new QueryEvent($query, $context);

        $this->dispatcher->dispatch($event);

        return $query;
    }
}
