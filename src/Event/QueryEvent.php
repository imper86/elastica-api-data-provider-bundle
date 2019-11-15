<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 14.11.2019
 * Time: 18:40
 */

namespace Imper86\ElasticaApiDataProviderBundle\Event;

use Elastica\Query;
use Symfony\Contracts\EventDispatcher\Event;

class QueryEvent extends Event
{
    /**
     * @var Query
     */
    private $query;
    /**
     * @var array
     */
    private $context;

    public function __construct(Query $query, array $context)
    {
        $this->query = $query;
        $this->context = $context;
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->context['filters'] ?? [];
    }

    public function markFilterAsResolved(string $filterKey): void
    {
        if (isset($this->context['filters'][$filterKey])) {
            unset($this->context['filters'][$filterKey]);
        }
    }
}
