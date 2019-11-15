<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 14.11.2019
 * Time: 18:43
 */

namespace Imper86\ElasticaApiDataProviderBundle\Factory;

use Elastica\Query;

/**
 * Interface QueryFactoryInterface
 * @package Imper86\ElasticaApiDataProviderBundle\Factory
 */
interface QueryFactoryInterface
{
    /**
     * @param array $context
     * @return Query
     */
    public function transform(array $context): Query;
}
