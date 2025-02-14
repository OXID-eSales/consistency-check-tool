<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageManagerService;
use OxidEsales\ConsistencyCheck\ImageManager\Utils\FileSystemUtilsInterface;
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\ImageHandlerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class ImageManagerServiceTest extends TestCase
{
    #[Test]
    public function itPerformsDryRunMoveSuccessfully(): void
    {
        $imageCollectionSpy = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionSpy
            ->method('getAll')
            ->willReturn([
                uniqid() => $this->createImageStub()
            ]);

        $fileSystemUtilsSpy = $this->createMock(FileSystemUtilsInterface::class);

        $imageHandlerSpy = $this->createMock(ImageHandlerInterface::class);
        $imageHandlerSpy
            ->expects($this->never())
            ->method('copy');
        $imageHandlerSpy
            ->expects($this->never())
            ->method('remove');


        $loggerSpy = $this->createMock(PsrLoggerInterface::class);
        $loggerSpy
            ->method('info')
            ->with($this->stringContains('[DRY-RUN] Move'));

        $sut = $this->getSut(
            fileSystemUtils: $fileSystemUtilsSpy,
            logger: $loggerSpy,
            imageHandler: $imageHandlerSpy
        );

        $sut->moveImages($imageCollectionSpy, uniqid(), true);

        $this->assertTrue(true);
    }

    #[Test]
    public function itPerformsDryRunDeleteSuccessfully(): void
    {
        $imageCollectionSpy = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionSpy
            ->method('getAll')
            ->willReturn([
                uniqid() => $this->createImageStub()
            ]);

        $imageHandlerSpy = $this->createMock(ImageHandlerInterface::class);
        $imageHandlerSpy
            ->expects($this->never())
            ->method('remove');

        $loggerSpy = $this->createMock(PsrLoggerInterface::class);
        $loggerSpy
            ->method('info')
            ->with($this->stringContains('[DRY-RUN] Delete'));

        $sut = $this->getSut(
            logger: $loggerSpy,
            imageHandler: $imageHandlerSpy
        );

        $sut->deleteImages($imageCollectionSpy, true);

        $this->assertTrue(true);
    }

    #[Test]
    public function itMovesImagesSuccessfully(): void
    {
        $destination = uniqid();
        $imageMock = $this->createImageStub(uniqid(), $imageName = uniqid(), $sourcePath = uniqid());

        $imageCollectionStub = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionStub
            ->method('getAll')
            ->willReturn([
                uniqid() => $imageMock
            ]);

        $imageHandlerSpy = $this->createMock(ImageHandlerInterface::class);
        $imageHandlerSpy->expects($this->once())
            ->method('copy')
            ->with($this->anything(), $this->anything());

        $imageHandlerSpy->expects($this->once())
            ->method('remove');

        $basePath = uniqid();
        $fileSystemUtilsSpy = $this->createMock(FileSystemUtilsInterface::class);
        $fileSystemUtilsSpy->expects($this->once())
            ->method('getAbsolutePath')
            ->with($sourcePath . '/' . $imageName)
            ->willReturn($basePath . '/' . $sourcePath . '/' . $imageName);


        $loggerSpy = $this->createMock(PsrLoggerInterface::class);

        $loggerSpy
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with($this->stringContains('Moved image:'));

        $sut = $this->getSut(
            fileSystemUtils: $fileSystemUtilsSpy,
            logger: $loggerSpy,
            imageHandler: $imageHandlerSpy
        );

        $movedCount = $sut->moveImages($imageCollectionStub, $destination);

        $this->assertSame(1, $movedCount);
    }

    #[Test]
    public function itLogsErrorWhenMoveFails(): void
    {
        $imageStub = $this->createImageStub();
        $imageCollectionStub = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionStub
            ->method('getAll')
            ->willReturn([
                uniqid() => $imageStub
            ]);

        $imageHandlerSpy = $this->createMock(ImageHandlerInterface::class);
        $imageHandlerSpy->method('copy')
            ->willThrowException(new \Exception());

        $loggerSpy = $this->createMock(PsrLoggerInterface::class);
        $loggerSpy
            ->method('error')
            ->with($this->stringStartsWith('Failed to move image:'));

        $sut = $this->getSut(
            logger: $loggerSpy,
            imageHandler: $imageHandlerSpy
        );

        $movedCount = $sut->moveImages(images: $imageCollectionStub, destination: uniqid());

        $this->assertSame(0, $movedCount);
    }

    #[Test]
    public function itHandlesNoImagesToMoveSuccessfully(): void
    {
        $imageCollectionStub = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionStub->method('getAll')->willReturn([]);

        $sut = $this->getSut();

        $movedCount = $sut->moveImages($imageCollectionStub, uniqid());

        $this->assertSame(0, $movedCount);
    }

    #[Test]
    public function itLogsErrorWhenDeleteFails(): void
    {
        $sourcePath = uniqid();
        $fileName = uniqid();

        $imageMock = $this->createImageStub(imageName: $fileName, directory: $sourcePath);
        $imageCollectionStub = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionStub
            ->method('getAll')
            ->willReturn([
                uniqid() => $imageMock
            ]);


        $imageHandlerSpy = $this->createMock(ImageHandlerInterface::class);
        $imageHandlerSpy->expects($this->once())
            ->method('remove')
            ->willThrowException(new \Exception());

        $loggerSpy = $this->createMock(PsrLoggerInterface::class);
        $loggerSpy
            ->method('error')
            ->with($this->stringContains('Failed to delete image'));

        $sut = $this->getSut(
            logger: $loggerSpy,
            imageHandler: $imageHandlerSpy
        );

        $deletedCount = $sut->deleteImages($imageCollectionStub);

        $this->assertSame(0, $deletedCount);
    }

    #[Test]
    public function itHandlesNoImagesToDeleteSuccessfully(): void
    {
        $imageCollectionStub = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionStub->method('getAll')->willReturn([]);

        $sut = $this->getSut();

        $deletedCount = $sut->deleteImages($imageCollectionStub);

        $this->assertSame(0, $deletedCount);
    }

    #[Test]
    public function itDeletesImagesSuccessfully(): void
    {
        $sourcePath = uniqid();
        $fileName = uniqid();

        $imageMock = $this->createImageStub(imageName: $fileName, directory: $sourcePath);
        $imageCollectionStub = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionStub
            ->method('getAll')
            ->willReturn([
                uniqid() => $imageMock
            ]);

        $imageHandlerSpy = $this->createMock(ImageHandlerInterface::class);
        $imageHandlerSpy->expects($this->once())
            ->method('remove')
            ->with($sourcePath . '/' . $fileName);

        $loggerSpy = $this->createMock(PsrLoggerInterface::class);
        $loggerSpy
            ->method('info')
            ->with(self::stringContains('Deleted image:'));

        $sut = $this->getSut(
            logger: $loggerSpy,
            imageHandler: $imageHandlerSpy
        );

        $deletedCount = $sut->deleteImages($imageCollectionStub);

        $this->assertSame(1, $deletedCount);
    }

    private function createImageStub(
        ?string $fieldName = null,
        ?string $imageName = null,
        ?string $directory = null,
    ): ImageDataTypeInterface {
        $imageStub = $this->createStub(ImageDataTypeInterface::class);
        $imageStub->method('getFieldName')->willReturn($fieldName ?? uniqid());
        $imageStub->method('getImageName')->willReturn($imageName ?? uniqid());
        $imageStub->method('getDirectory')->willReturn($directory ?? uniqid());

        return $imageStub;
    }

    private function getSut(
        ?FileSystemUtilsInterface $fileSystemUtils = null,
        ?PsrLoggerInterface $logger = null,
        ?ImageHandlerInterface $imageHandler = null,
    ): ImageManagerService {
        $fileSystemUtils ??= $this->createMock(FileSystemUtilsInterface::class);
        $logger ??= $this->createMock(PsrLoggerInterface::class);
        $imageHandler ??= $this->createMock(ImageHandlerInterface::class);

        return new ImageManagerService(
            fileSystemUtils: $fileSystemUtils,
            logger: $logger,
            imageHandler: $imageHandler
        );
    }
}
