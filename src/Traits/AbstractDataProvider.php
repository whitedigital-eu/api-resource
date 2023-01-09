<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Traits;

use ApiPlatform\Doctrine\Orm\Extension\FilterExtension;
use ApiPlatform\Doctrine\Orm\Extension\OrderExtension;
use ApiPlatform\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;

use function count;
use function is_array;
use function sprintf;
use function strtolower;

trait AbstractDataProvider
{
    /**
     * @throws ReflectionException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            return $this->getCollection($operation, $context);
        }

        return $this->getItem($operation, $uriVariables['id'], $context);
    }

    protected function getCollection(?Operation $operation = null, array $context = []): array|object
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->select('e')->from($resourceClass = $this->getEntityClass($operation), 'e');

        $this->authorizationService->limitGetCollection($resourceClass, $queryBuilder);

        return $this->applyFilterExtensionsToCollection($queryBuilder, new QueryNameGenerator(), $operation, $context);
    }

    protected function getEntityClass(Operation $operation): string
    {
        return $this->classMapper->byResource($operation->getClass());
    }

    protected function applyFilterExtensionsToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, ?Operation $operation = null, array $context = []): array|object
    {
        foreach ($this->collectionExtensions as $extension) {
            if ($extension instanceof FilterExtension
                || $extension instanceof QueryResultCollectionExtensionInterface) {
                $extension->applyToCollection($queryBuilder, $queryNameGenerator, $operation?->getClass(), $operation, $context);
            }

            if ($extension instanceof OrderExtension) {
                $orderByDqlPart = $queryBuilder->getDQLPart('orderBy');
                if (is_array($orderByDqlPart) && count($orderByDqlPart) > 0) {
                    continue;
                }

                foreach ($operation?->getOrder() as $field => $direction) {
                    $queryBuilder->addOrderBy(sprintf('%s.%s', $queryBuilder->getRootAliases()[0], $field), $direction);
                }
            }

            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($operation?->getClass(), $operation, $context)) {
                return $extension->getResult($queryBuilder, $operation?->getClass(), $operation, $context);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @throws ReflectionException
     */
    protected function getItem(Operation $operation, mixed $id, array $context): object
    {
        $entity = $this->entityManager->getRepository($entityClass = $this->getEntityClass($operation))->find($id);

        $this->throwErrorIfNotExists($entity, strtolower((new ReflectionClass($entityClass))->getShortName()), $id);
        $this->authorizationService->authorizeSingleObject($entity, AuthorizationService::ITEM_GET);

        return $this->createResource($entity, $context);
    }

    protected function throwErrorIfNotExists(mixed $result, string $rootAlias, mixed $id): void
    {
        if (null === $result) {
            throw new NotFoundHttpException(sprintf('Resurss %s:%s nav atrasts.', $rootAlias, $id));
        }
    }

    abstract protected function createResource(BaseEntity $entity, array $context);

    protected function queryResult(QueryBuilder $queryBuilder): BaseEntity
    {
        $entity = $queryBuilder->getQuery()->getResult()[0] ?? null;

        $this->throwErrorIfNotExists($entity, $queryBuilder->getRootAliases()[0], $queryBuilder->getParameter('id')?->getValue());

        return $entity;
    }
}
