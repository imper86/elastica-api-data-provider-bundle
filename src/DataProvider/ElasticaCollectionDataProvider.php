<?php
/**
 * Author: Adrian Szuszkiewicz <me@imper.info>
 * Github: https://github.com/imper86
 * Date: 14.11.2019
 * Time: 18:34
 */

namespace Imper86\ElasticaApiDataProviderBundle\DataProvider;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Imper86\ElasticaApiDataProviderBundle\Factory\QueryFactoryInterface;
use Imper86\ElasticaApiDataProviderBundle\Model\ElasticaCollectionAwareEntityInterface;
use Imper86\ElasticaApiDataProviderBundle\Model\Paginator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ElasticaCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var RepositoryManagerInterface
     */
    private $repositoryManager;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var QueryFactoryInterface
     */
    private $queryFactory;
    /**
     * @var array
     */
    private $config;

    public function __construct(
        array $config,
        RepositoryManagerInterface $repositoryManager,
        ParameterBagInterface $parameterBag,
        QueryFactoryInterface $queryFactory
    )
    {
        $this->repositoryManager = $repositoryManager;
        $this->parameterBag = $parameterBag;
        $this->queryFactory = $queryFactory;
        $this->config = $config;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $repository = $this->repositoryManager->getRepository($resourceClass);
        $query = $this->queryFactory->transform($context);

        $defaultLimit = $this->parameterBag->get('api_platform.collection.pagination.items_per_page');
        $maxLimit = $this->parameterBag->get('api_platform.collection.pagination.maximum_items_per_page');
        $pageParameterName = $this->parameterBag->get('api_platform.collection.pagination.page_parameter_name');
        $limitParameterName = $this->parameterBag->get('api_platform.collection.pagination.items_per_page_parameter_name');

        $limit = $this->context['filters'][$limitParameterName] ?? $defaultLimit;

        if ($limit > $maxLimit) {
            $limit = $maxLimit;
        }

        return new Paginator(
            $repository->createPaginatorAdapter($query),
            (int)($this->context['filters'][$pageParameterName] ?? 1),
            (int)$limit
        );
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $supports = is_subclass_of($resourceClass, ElasticaCollectionAwareEntityInterface::class) ||
            in_array($resourceClass, $this->config['resources'] ?? []);

        return 'get' === $operationName && $supports;
    }
}
