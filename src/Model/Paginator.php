<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 14.11.2019
 * Time: 18:36
 */

namespace Imper86\ElasticaApiDataProviderBundle\Model;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use FOS\ElasticaBundle\Paginator\PartialResultsInterface;
use FOS\ElasticaBundle\Paginator\RawPartialResults;
use FOS\ElasticaBundle\Paginator\TransformedPartialResults;

class Paginator implements \IteratorAggregate, PaginatorInterface
{
    /**
     * @var PaginatorAdapterInterface
     */
    private $adapter;
    /**
     * @var int
     */
    private $page;
    /**
     * @var int
     */
    private $limit;
    /**
     * @var PartialResultsInterface|RawPartialResults|TransformedPartialResults
     */
    private $results;

    public function __construct(PaginatorAdapterInterface $adapter, int $page, int $limit)
    {
        $this->adapter = $adapter;
        $this->page = $page;
        $this->limit = $limit;
        $this->results = $this->adapter->getResults(($page - 1) * $limit, $limit);
    }

    public function getIterator()
    {
        foreach ($this->results->toArray() as $entity) {
            yield $entity;
        }
    }

    public function count()
    {
        return count($this->results->toArray());
    }

    public function getLastPage(): float
    {
        return ceil($this->getTotalItems() / $this->limit);
    }

    public function getTotalItems(): float
    {
        return $this->results->getTotalHits();
    }

    public function getCurrentPage(): float
    {
        return $this->page;
    }

    public function getItemsPerPage(): float
    {
        return $this->limit;
    }
}
