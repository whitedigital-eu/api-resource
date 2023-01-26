<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use ArrayObject;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use WhiteDigital\ApiResource\Controller\CreateStorageObjectController;
use WhiteDigital\ApiResource\DataProcessor\StorageDataProcessor;
use WhiteDigital\ApiResource\DataProvider\StorageDataProvider;
use WhiteDigital\ApiResource\Entity\Storage;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;

#[
    ApiResource(
        shortName: 'Storage',
        types: ['https://schema.org/MediaObject', ],
        operations: [
            new Post(
                uriTemplate: '/storage',
                controller: CreateStorageObjectController::class,
                openapi: new Model\Operation(
                    requestBody: new Model\RequestBody(
                        content: new ArrayObject([
                            'multipart/form-data' => [
                                'schema' => [
                                    'type' => Type::BUILTIN_TYPE_OBJECT,
                                    'properties' => [
                                        'file' => [
                                            'type' => Type::BUILTIN_TYPE_STRING,
                                            'format' => 'binary',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ),
                validationContext: ['groups' => ['Default', self::WRITE, ], ],
                deserialize: false,
            ),
            new Get(
                uriTemplate: '/storage/{id}',
            ),
            new Delete(
                uriTemplate: '/storage/{id}',
            ),
        ],
        routePrefix: '/wd/ar',
        normalizationContext: ['groups' => [self::READ, ], ],
        provider: StorageDataProvider::class,
        processor: StorageDataProcessor::class,
    )
]
#[Vich\Uploadable]
#[Mapping(Storage::class)]
class StorageResource extends BaseResource
{
    use Traits\CreatedUpdated;
    use Traits\Groups;

    public const PREFIX = 'storage:';

    #[ApiProperty(identifier: true)]
    #[Groups([self::READ, ])]
    public mixed $id = null;

    #[ApiProperty(types: ['https://schema.org/contentUrl', ])]
    #[Groups([self::READ, ])]
    public ?string $contentUrl = null;

    #[Assert\NotNull(groups: [self::WRITE, ])]
    #[Assert\File(groups: [self::WRITE, ])]
    #[Vich\UploadableField(
        mapping: 'wd_ar_media_object',
        fileNameProperty: 'filePath',
        size: 'size',
        mimeType: 'mimeType',
        originalName: 'originalName',
        dimensions: 'dimensions',
    )]
    public ?File $file = null;

    #[Groups([self::READ, ])]
    public ?string $filePath = null;

    #[Groups([self::READ, ])]
    public ?int $size = null;

    #[Groups([self::READ, ])]
    public ?string $mimeType = null;

    #[Groups([self::READ, ])]
    public ?string $originalName = null;

    #[Groups([self::READ, ])]
    public ?array $dimensions = null;
}
