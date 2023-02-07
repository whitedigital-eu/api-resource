<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Storage\StorageInterface;
use WhiteDigital\ApiResource\ApiResource\StorageItemResource;
use WhiteDigital\ApiResource\Entity\StorageItem;
use WhiteDigital\ApiResource\Traits\Override;
use WhiteDigital\EntityResourceMapper\Security\AuthorizationService;

#[AsController]
class CreateStorageObjectController extends AbstractController
{
    use Override;

    public function __construct(private readonly AuthorizationService $authorizationService)
    {
    }

    public function __invoke(Request $request, EntityManagerInterface $em, StorageInterface $vichStorage, TranslatorInterface $translator): StorageItemResource
    {
        if (!$request->files->has($key = 'file')) {
            throw new BadRequestHttpException($translator->trans('named_required_parameter_is_missing', ['parameter' => 'file'], domain: 'ApiResource'));
        }

        $uploadedFile = $request->files->get($key);

        if (!$uploadedFile instanceof UploadedFile) {
            throw new BadRequestHttpException($translator->trans('named_required_parameter_is_incorrect', ['parameter' => 'file'], domain: 'ApiResource'));
        }

        if ($uploadedFile->getError()) {
            throw new BadRequestHttpException($translator->trans($uploadedFile->getErrorMessage()));
        }

        $storage = (new StorageItem())->setFile($uploadedFile);

        $this->authorizationService->setAuthorizationOverride(fn () => $this->override(AuthorizationService::COL_POST, StorageItemResource::class));
        $this->authorizationService->authorizeSingleObject($storage, AuthorizationService::COL_POST);

        $em->persist($storage);
        $em->flush();

        $mediaObject = new StorageItemResource();
        $mediaObject->id = $storage->getId();
        $mediaObject->filePath = $storage->getFilePath();
        $mediaObject->file = $storage->getFile();
        $mediaObject->size = $storage->getSize();
        $mediaObject->mimeType = $storage->getMimeType();
        $mediaObject->dimensions = $storage->getDimensions();
        $mediaObject->originalName = $storage->getOriginalName();
        $mediaObject->contentUrl = $vichStorage->resolveUri($storage, 'file');
        $mediaObject->createdAt = $storage->getCreatedAt();

        return $mediaObject;
    }
}
