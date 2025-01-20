<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Exception\ImageDatabaseRepositoryException;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageCollectionFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Repository\ImageRepositoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageCheckerService;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageCheckerServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class ImageCheckerServiceTest extends TestCase
{
    #[Test]
    public function itFindsUnusedImagesSuccessfully(): void
    {
        $directoryImage1 = $databaseImage1 = $this->createStub(ImageDataTypeInterface::class);
        $databaseImage2 = $this->createStub(ImageDataTypeInterface::class);
        $directoryImage3 = $this->createStub(ImageDataTypeInterface::class);

        $databaseImages = $this->createImageCollection([$databaseImage1, $databaseImage2]);
        $directoryImages = $this->createImageCollection([$directoryImage1, $directoryImage3]);

        $databaseRepositoryStub = $this->createStub(ImageRepositoryInterface::class);
        $databaseRepositoryStub
            ->method('getImages')
            ->willReturn($databaseImages);

        $imageDirectoryRepositoryStub = $this->createStub(ImageRepositoryInterface::class);
        $imageDirectoryRepositoryStub
            ->method('getImages')
            ->willReturn($directoryImages);

        $addedImages = [];
        $unusedImagesMock = $this->createMock(ImageCollectionInterface::class);

        $unusedImagesMock
            ->method('add')
            ->willReturnCallback(function ($image) use (&$addedImages) {
                $addedImages[] = $image;
            });

        $imageCollectionFactoryMock = $this->createMock(ImageCollectionFactoryInterface::class);
        $imageCollectionFactoryMock
            ->method('create')
            ->willReturn($unusedImagesMock);

        $sut = $this->getSut(
            imageDatabaseRepository: $databaseRepositoryStub,
            imageDirectoryRepository: $imageDirectoryRepositoryStub,
            imageCollectionFactory: $imageCollectionFactoryMock
        );

        $entityStub = $this->createStub(ImageEntityInterface::class);
        $result = $sut->getUnusedImages($entityStub);

        $this->assertSame($unusedImagesMock, $result);
        $this->assertCount(1, $addedImages);
        $this->assertSame($directoryImage3, $addedImages[0]);
    }

    #[Test]
    public function itReturnsEmptyCollectionWhenAllImagesAreUsed(): void
    {
        $databaseImage1 = $this->createStub(ImageDataTypeInterface::class);
        $directoryImage1 = $this->createStub(ImageDataTypeInterface::class);

        $databaseImages = $this->createImageCollection([$databaseImage1]);
        $directoryImages = $this->createImageCollection([$directoryImage1]);

        $databaseRepositoryStub = $this->createStub(ImageRepositoryInterface::class);
        $databaseRepositoryStub
            ->method('getImages')
            ->willReturn($databaseImages);

        $imageDirectoryRepositoryStub = $this->createMock(ImageRepositoryInterface::class);
        $imageDirectoryRepositoryStub
            ->method('getImages')
            ->willReturn($directoryImages);

        $emptyCollectionMock = $this->createImageCollection([]);

        $imageCollectionFactoryMock = $this->createMock(ImageCollectionFactoryInterface::class);
        $imageCollectionFactoryMock
            ->method('create')
            ->willReturn($emptyCollectionMock);

        $sut = $this->getSut(
            imageDatabaseRepository: $databaseRepositoryStub,
            imageDirectoryRepository: $imageDirectoryRepositoryStub,
            imageCollectionFactory: $imageCollectionFactoryMock
        );

        $entityStub = $this->createStub(ImageEntityInterface::class);
        $result = $sut->getUnusedImages($entityStub);

        $this->assertSame($emptyCollectionMock, $result);
    }

    #[Test]
    public function itLogsErrorAndReturnsEmptyCollectionOnException(): void
    {
        $entityStub = $this->createStub(ImageEntityInterface::class);

        $directoryRepositoryStub = $this->createStub(ImageRepositoryInterface::class);
        $databaseRepositoryStub = $this->createStub(ImageRepositoryInterface::class);
        $databaseRepositoryStub
            ->method('getImages')
            ->willThrowException(new ImageDatabaseRepositoryException(uniqid(), uniqid()));

        $psrLoggerMock = $this->createMock(PsrLoggerInterface::class);
        $psrLoggerMock
            ->expects($this->atLeastOnce())
            ->method('error')
            ->with($this->stringContains('Error processing entity'));

        $emptyImageCollectionStub = $this->createStub(ImageCollectionInterface::class);

        $imageCollectionFactoryStub = $this->createMock(ImageCollectionFactoryInterface::class);
        $imageCollectionFactoryStub
            ->method('create')
            ->willReturn($emptyImageCollectionStub);

        $sut = $this->getSut(
            imageDatabaseRepository: $databaseRepositoryStub,
            imageDirectoryRepository: $directoryRepositoryStub,
            imageCollectionFactory: $imageCollectionFactoryStub,
            logger: $psrLoggerMock,
        );

        $actualImages = $sut->getUnusedImages($entityStub);

        $this->assertInstanceOf(ImageCollectionInterface::class, $actualImages);
        $this->assertSame($emptyImageCollectionStub, $actualImages);
    }

    private function createImageCollection(array $images): ImageCollectionInterface
    {
        $imageCollection = $this->createMock(ImageCollectionInterface::class);
        $imageCollection->method('getAll')->willReturn($images);

        $imageCollection->method('contains')
            ->willReturnCallback(function ($image) use ($images) {
                return in_array($image, $images, true);
            });

        return $imageCollection;
    }

    private function getSut(
        ?ImageRepositoryInterface $imageDatabaseRepository = null,
        ?ImageRepositoryInterface $imageDirectoryRepository = null,
        ?ImageCollectionFactoryInterface $imageCollectionFactory = null,
        ?PsrLoggerInterface $logger = null,
    ): ImageCheckerServiceInterface {
        $imageDatabaseRepository ??= $this->createStub(ImageRepositoryInterface::class);
        $imageDirectoryRepository ??= $this->createStub(ImageRepositoryInterface::class);
        $imageCollectionFactory ??= $this->createStub(ImageCollectionFactoryInterface::class);
        $logger ??= $this->createStub(PsrLoggerInterface::class);
        return new ImageCheckerService(
            imageDatabaseRepository: $imageDatabaseRepository,
            imageDirectoryRepository: $imageDirectoryRepository,
            imageCollectionFactory: $imageCollectionFactory,
            logger: $logger,
        );
    }
}
