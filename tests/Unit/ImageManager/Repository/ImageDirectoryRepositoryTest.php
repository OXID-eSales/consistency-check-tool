<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Utils;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollection;
use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageCollectionFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageDataTypeFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageDirectoryRepository;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageDirectoryRepositoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageRepositoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Utils\FileSystemUtilsInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ImageDirectoryRepositoryTest extends TestCase
{
    #[Test]
    public function itScansEntityDirectoryReturnsImageCollection(): void
    {
        $directoryPath = uniqid();
        $testFieldName = uniqid();
        $fileNames = [$fileName1 = uniqid(), $fileName2 = uniqid()];

        $fileSystemUtilsMock = $this->createMock(FileSystemUtilsInterface::class);
        $fileSystemUtilsMock
            ->method('directoryExists')
            ->with($directoryPath)
            ->willReturn(true);

        $fileSystemUtilsMock
            ->method('getFilesInDirectory')
            ->with($directoryPath)
            ->willReturn($fileNames);

        $imageCollectionFactoryMock = $this->createMock(ImageCollectionFactoryInterface::class);
        $imageCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn(new ImageCollection());

        $imageFactoryMock = $this->createMock(ImageDataTypeFactoryInterface::class);
        $imageFactoryMock->expects($this->exactly(2))
            ->method('createFromFileDetails')
            ->willReturnCallback(function ($fieldName, $fileName, $path) use (&$createdImages) {
                $createdImages[] = [$fieldName, $fileName, $path];
                return $this->createMock(ImageDataTypeInterface::class);
            });

        $sut = $this->getSut(
            fileSystemUtils: $fileSystemUtilsMock,
            imageFactory: $imageFactoryMock,
            imageCollectionFactory: $imageCollectionFactoryMock,
        );

        $entityStub = $this->createStub(ImageEntityInterface::class);
        $entityStub->method('getDirectory')->willReturn($directoryPath);
        $entityStub->method('getFieldName')->willReturn($testFieldName);

        $imageCollection = $sut->getImages($entityStub);

        $this->assertInstanceOf(ImageCollectionInterface::class, $imageCollection);
        $this->assertCount(2, $imageCollection->getAll());
        $this->assertSame([
            [$testFieldName, $fileName1, $directoryPath],
            [$testFieldName, $fileName2, $directoryPath],
        ], $createdImages);
    }

    #[Test]
    public function itReturnsEmptyImageCollectionWhenDirectoryDoesNotExist(): void
    {
        $directoryPath = uniqid();

        $fileSystemUtilsMock = $this->createMock(FileSystemUtilsInterface::class);
        $fileSystemUtilsMock
            ->method('directoryExists')
            ->with($directoryPath)
            ->willReturn(false);

        $imageCollectionFactoryMock = $this->createMock(ImageCollectionFactoryInterface::class);
        $imageCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn(new ImageCollection());


        $imageFactoryMock = $this->createMock(ImageDataTypeFactoryInterface::class);
        $imageFactoryMock->expects($this->never())
            ->method('createFromFileDetails');

        $sut = $this->getSut(
            fileSystemUtils: $fileSystemUtilsMock,
            imageFactory: $imageFactoryMock,
            imageCollectionFactory: $imageCollectionFactoryMock,
        );

        $entityStub = $this->createStub(ImageEntityInterface::class);
        $entityStub->method('getDirectory')->willReturn($directoryPath);

        $result = $sut->getImages($entityStub);


        $this->assertInstanceOf(ImageCollectionInterface::class, $result);
        $this->assertCount(0, $result->getAll());
    }

    private function getSut(
        ?FileSystemUtilsInterface $fileSystemUtils = null,
        ?ImageDataTypeFactoryInterface $imageFactory = null,
        ?ImageCollectionFactoryInterface $imageCollectionFactory = null,
    ): ImageRepositoryInterface {
            $fileSystemUtils ??= $this->createStub(FileSystemUtilsInterface::class);
            $imageFactory ??= $this->createStub(ImageDataTypeFactoryInterface::class);
            $imageCollectionFactory ??= $this->createStub(ImageCollectionFactoryInterface::class);
        return new ImageDirectoryRepository(
            fileSystemUtils: $fileSystemUtils,
            imageFactory: $imageFactory,
            imageCollectionFactory: $imageCollectionFactory
        );
    }
}
