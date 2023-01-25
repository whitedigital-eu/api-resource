<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use WhiteDigital\ApiResource\ApiResource\StorageResource;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Entity\Traits\Id;

#[ORM\Entity]
#[Vich\Uploadable]
#[Mapping(StorageResource::class)]
class Storage extends BaseEntity
{
    use Id;

    #[ORM\Column(nullable: false)]
    private ?string $filePath = null;

    #[Vich\UploadableField(mapping: 'wd_ar_media_object', fileNameProperty: 'filePath')]
    private ?File $file = null;

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }
}
