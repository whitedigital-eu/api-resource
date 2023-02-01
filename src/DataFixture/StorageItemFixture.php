<?php declare(strict_types = 1);

namespace WhiteDigital\ApiResource\DataFixture;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use WhiteDigital\ApiResource\Entity\StorageItem;

use function copy;

class StorageItemFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $templateFileName = __DIR__ . '/assets/storage_item-fixture.template.txt';
        for ($i = 0; $i < 3; $i++) {
            $fileName = __DIR__ . '/assets/storage_iten-' . $i . '.txt';

            copy($templateFileName, $fileName);
            $file = new UploadedFile($fileName, 'storage_item-' . $i . '.txt', 'text/plain', test: true);

            $fixture = (new StorageItem())->setFile($file);

            $manager->persist($fixture);
            $manager->flush();

            $this->addReference('wdFile' . $i, $fixture);
        }
    }
}
