<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\ImageManager\Service;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\ImageManager\Dto\ImageCollection;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntity;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageCollectionFactory;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageDataTypeFactory;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageDirectoryRepository;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageRepositoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageUsageChecker;
use OxidEsales\ConsistencyCheck\ImageManager\Service\UnusedImageFinderService;
use OxidEsales\ConsistencyCheck\ImageManager\Utils\FileSystemUtils;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolver;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

final class UnusedImageFinderServiceTest extends TestCase
{
    private vfsStreamDirectory $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = vfsStream::setup('source');
    }

    #[Test]
    public function doesNotDetectUsedImagesAsUnused(): void
    {
        $imageDirectory = 'out/pictures/master/product/2';
        $usedImageName = uniqid('used_') . '.jpg';
        $unusedImageName = uniqid('unused_') . '.jpg';

        $this->createDirectoryStructure($imageDirectory);
        $this->createTestImage($imageDirectory, $usedImageName);
        $this->createTestImage($imageDirectory, $unusedImageName);

        $entity = new ImageEntity(
            name: 'Product',
            table: 'oxarticles',
            fieldName: 'OXPIC2',
            directory: $imageDirectory,
        );

        $imageDataTypeFactory = new ImageDataTypeFactory();
        $usedImagesCollection = new ImageCollection();
        $usedImagesCollection->add(
            $imageDataTypeFactory->createFromFileDetails('OXPIC2', $usedImageName, $imageDirectory)
        );

        $sut = $this->getSut(
            databaseImages: $usedImagesCollection,
        );

        $unusedImages = $sut->getUnusedImages($entity);

        $unusedImagesArray = array_values($unusedImages->getAll());
        $this->assertCount(1, $unusedImagesArray);
        $this->assertSame($unusedImageName, $unusedImagesArray[0]->getImageName());
    }

    #[Test]
    public function detectsUnusedImagesWithDefaultConfiguration(): void
    {
        $servicesYamlPath = __DIR__ . '/../../../../src/ImageManager/Entity/services.yaml';
        $config = Yaml::parseFile($servicesYamlPath);

        $firstServiceConfig = reset($config['services']);
        $configuredDirectory = $firstServiceConfig['arguments']['$directory'];

        $relativeDirectory = ltrim($configuredDirectory, '/');

        $testImageName = uniqid('test_image_') . '.jpg';

        $this->createDirectoryStructure($relativeDirectory);
        $this->createTestImage($relativeDirectory, $testImageName);

        $entity = new ImageEntity(
            name: $firstServiceConfig['arguments']['$name'],
            table: $firstServiceConfig['arguments']['$table'],
            fieldName: $firstServiceConfig['arguments']['$fieldName'],
            directory: $configuredDirectory,
        );

        $sut = $this->getSut(
            databaseImages: new ImageCollection(),
        );

        $unusedImages = $sut->getUnusedImages($entity);

        $this->assertCount(
            1,
            $unusedImages->getAll(),
            sprintf(
                "Expected to find 1 unused image but found %d. "
                . "If directory path in services.yaml starts with '/', it will be treated as absolute path "
                . "and images won't be found. Current configured path: '%s'",
                count($unusedImages->getAll()),
                $configuredDirectory
            )
        );

        $foundImageNames = array_map(
            fn($image) => $image->getImageName(),
            $unusedImages->getAll()
        );
        $this->assertContains($testImageName, $foundImageNames);
    }

    private function createDirectoryStructure(string $path): void
    {
        $parts = explode('/', $path);
        $current = $this->fileSystem;

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $child = $current->getChild($part);
            if ($child === null) {
                $child = vfsStream::newDirectory($part)->at($current);
            }
            $current = $child;
        }
    }

    private function createTestImage(string $directory, string $filename): void
    {
        $fullPath = $this->fileSystem->url() . '/' . $directory . '/' . $filename;
        file_put_contents($fullPath, uniqid());
    }

    private function getSut(
        ImageCollection $databaseImages,
    ): UnusedImageFinderService {
        $contextStub = $this->createStub(ContextInterface::class);
        $contextStub->method('getSourcePath')->willReturn($this->fileSystem->url());

        $pathResolver = new PathResolver($contextStub);
        $fileSystemUtils = new FileSystemUtils(new Finder(), $pathResolver);
        $imageCollectionFactory = new ImageCollectionFactory();
        $imageDataTypeFactory = new ImageDataTypeFactory();

        $imageDirectoryRepository = new ImageDirectoryRepository(
            $fileSystemUtils,
            $imageDataTypeFactory,
            $imageCollectionFactory,
        );

        $imageDatabaseRepositoryStub = $this->createStub(ImageRepositoryInterface::class);
        $imageDatabaseRepositoryStub->method('getImages')->willReturn($databaseImages);

        return new UnusedImageFinderService(
            imageDatabaseRepository: $imageDatabaseRepositoryStub,
            imageDirectoryRepository: $imageDirectoryRepository,
            imageCollectionFactory: $imageCollectionFactory,
            imageUsageChecker: new ImageUsageChecker(),
            logger: new NullLogger(),
        );
    }
}
