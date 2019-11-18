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
use Elastica\Query;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Imper86\ElasticaApiDataProviderBundle\DataProvider\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var RepositoryManagerInterface
     */
    private $repositoryManager;
    /**
     * @var ExtensionInterface[]|iterable
     */
    private $collectionExtensions;
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @param ParameterBagInterface $parameterBag
     * @param RepositoryManagerInterface $repositoryManager
     * @param ExtensionInterface[] $collectionExtensions
     */
    public function __construct(
        ParameterBagInterface $parameterBag,
        RepositoryManagerInterface $repositoryManager,
        iterable $collectionExtensions = []
    )
    {
        $this->repositoryManager = $repositoryManager;
        $this->collectionExtensions = $collectionExtensions;
        $this->parameterBag = $parameterBag;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $query = new Query(new Query\BoolQuery());

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($query, $resourceClass, $operationName, $context);
        }

        $repository = $this->repositoryManager->getRepository($resourceClass);

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
        $supports = is_subclass_of($resourceClass, ElasticaCollectionAwareInterface::class) ||
            in_array($resourceClass, $this->config['resources'] ?? []);

        return 'get' === $operationName && $supports;
    }
}
