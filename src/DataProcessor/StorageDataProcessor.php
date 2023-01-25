<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\DataProcessor;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use WhiteDigital\ApiResource\Entity\Storage;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;

use function preg_match;

final readonly class StorageDataProcessor implements ProcessorInterface
{
    public function __construct(
        private AuthorizationService $authorizationService,
        private EntityManagerInterface $entityManager,
        protected TranslatorInterface $translator,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($operation instanceof DeleteOperationInterface) {
            $this->remove($data);
        }
    }

    protected function remove(BaseResource $resource): void
    {
        $this->authorizationService->authorizeSingleObject($resource, AuthorizationService::ITEM_DELETE);
        $entity = $this->entityManager->getRepository(Storage::class)->find($resource->id);
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
            throw new AccessDeniedHttpException($this->translator->trans('unable_to_delete_record', ['detail' => $matches[1]], domain: 'ApiResource'), $exception);
        }
    }
}
