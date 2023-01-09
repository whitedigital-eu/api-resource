<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Traits;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;

use function preg_match;
use function sprintf;

trait AbstractDataProcessor
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        if (!$operation instanceof DeleteOperationInterface) {
            if ($operation instanceof Post) {
                $entity = $this->post($data, $context);
            } else {
                $entity = $this->patch($data, $context);
            }

            $this->flushAndRefresh($entity);

            return $this->createResource($entity, $context);
        }

        $this->remove($data);

        return null;
    }

    protected function patch(mixed $data, array $context = []): ?BaseEntity
    {
        $this->authorizationService->authorizeSingleObject($data, AuthorizationService::ITEM_PATCH);
        $existingEntity = $this->findById($this->getEntityClass(), $data->id);

        return $this->createEntity($data, $context, $existingEntity);
    }

    protected function post(mixed $data, array $context = []): ?BaseEntity
    {
        $this->authorizationService->authorizeSingleObject($data, AuthorizationService::COL_POST);

        return $this->createEntity($data, $context);
    }

    abstract protected function createEntity(BaseResource $resource, array $context, ?BaseEntity $existingEntity = null);

    protected function findById(string $class, int $id): ?BaseEntity
    {
        return $this->entityManager->getRepository($class)->find($id);
    }

    abstract protected function getEntityClass(): string;

    protected function flushAndRefresh(BaseEntity $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        $this->entityManager->refresh($entity);
    }

    abstract protected function createResource(BaseEntity $entity, array $context);

    protected function remove(BaseResource $resource): void
    {
        $this->authorizationService->authorizeSingleObject($resource, AuthorizationService::ITEM_DELETE);
        $entity = $this->findById($this->getEntityClass(), $resource->id);
        if (null !== $entity) {
            $this->removeWithFkCheck($entity);
        }
    }

    protected function removeWithFkCheck(BaseEntity $entity): void
    {
        $this->entityManager->remove($entity);

        try {
            $this->entityManager->flush();
        } catch (Exception $exception) {
            preg_match('/DETAIL: (.*)/', $exception->getMessage(), $matches);
            throw new AccessDeniedHttpException(sprintf('Šo ierakstu nav iespējams dzēst, jo tas tiek izmantots citur (%s).', $matches[1]), $exception);
        }
    }
}
