<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 15.11.2019
 * Time: 19:43
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Filter;

use ApiPlatform\Core\Api\FilterInterface as BaseFilterInterface;
use Elastica\Query;

interface FilterInterface extends BaseFilterInterface
{
    public function apply(Query $query, string $resourceClass, $operationName, $context);
}
