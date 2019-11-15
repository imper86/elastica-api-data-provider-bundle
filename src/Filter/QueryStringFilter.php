<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 15.11.2019
 * Time: 16:44
 */

namespace Imper86\ElasticaApiDataProviderBundle\Filter;

use ApiPlatform\Core\Api\FilterInterface;

class QueryStringFilter implements FilterInterface
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getDescription(string $resourceClass): array
    {
        $paramName = $this->config['query_string_filter']['parameter_name'];

        return [
            $paramName => [
                'property' => null,
                'type' => 'string',
                'required' => false,
                'swagger' => [
                    'description' => 'Query string filter, you can use Lucene with this',
                    'name' => $paramName,
                    'type' => 'string',
                ]
            ],
        ];
    }
}
