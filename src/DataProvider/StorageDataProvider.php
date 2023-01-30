<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\DataProvider;

use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ReflectionException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use WhiteDigital\ApiResource\ApiResource\StorageItemResource;
use WhiteDigital\ApiResource\Php82\AbstractDataProvider;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;

final readonly class StorageDataProvider extends AbstractDataProvider
{
    /**
     * @throws ReflectionException
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            throw new NotFoundHttpException($this->translator->trans('resource_not_found', domain: 'ApiResource'));
        }

        return $this->getItem($operation, $uriVariables['id'], $context);
    }

    /**
     * @throws ExceptionInterface
     * @throws ResourceClassNotFoundException
     * @throws ReflectionException
     */
    protected function createResource(BaseEntity $entity, array $context): StorageItemResource
    {
        return StorageItemResource::create($entity, $context);
    }
}
