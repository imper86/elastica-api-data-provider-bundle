<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 15.11.2019
 * Time: 19:24
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider\Extension;

use Elastica\Query;

interface ExtensionInterface
{
    public function applyToCollection(Query $query, string $resourceClass, string $operationName, array $context);
}
