<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Exception\FileSystemException;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageManagerService;
use OxidEsales\ConsistencyCheck\ImageManager\Utils\FileSystemUtilsInterface;
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
        $fileSystemUtilsSpy
            ->expects($this->never())
            ->method('moveFile');

        $loggerSpy = $this->createMock(PsrLoggerInterface::class);
        $loggerSpy
            ->method('info')
            ->with($this->stringContains('[DRY-RUN] Move'));

        $sut = $this->getSut(
            fileSystemUtils: $fileSystemUtilsSpy,
            logger: $loggerSpy
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

        $fileSystemUtilsSpy = $this->createMock(FileSystemUtilsInterface::class);
        $fileSystemUtilsSpy
            ->expects($this->never())
            ->method('deleteFile');

        $loggerSpy = $this->createMock(PsrLoggerInterface::class);
        $loggerSpy
            ->method('info')
            ->with($this->stringContains('[DRY-RUN] Delete'));

        $sut = $this->getSut(
            fileSystemUtils: $fileSystemUtilsSpy,
            logger: $loggerSpy
        );

        $sut->deleteImages($imageCollectionSpy, true);

        $this->assertTrue(true);
    }

    #[Test]
    public function itMovesImagesSuccessfully(): void
    {
        $destination = uniqid();
        $imageMock = $this->createImageStub(uniqid(), $imageName = uniqid(), $sourcePath = uniqid());

        $imageCollectionSpy = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionSpy
            ->method('getAll')
            ->willReturn([
                uniqid() => $imageMock
            ]);

        $fileSystemUtilsSpy = $this->createMock(FileSystemUtilsInterface::class);
        $fileSystemUtilsSpy->expects($this->once())
            ->method('moveFile')
            ->with($sourcePath . '/' . $imageName, $destination . '/' . $imageName);

        $loggerSpy = $this->createMock(PsrLoggerInterface::class);

        $loggerSpy
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with($this->stringContains('Moved image:'));

        $sut = $this->getSut(
            fileSystemUtils: $fileSystemUtilsSpy,
            logger: $loggerSpy
        );

        $sut->moveImages($imageCollectionSpy, $destination);
    }

    #[Test]
    public function itLogsErrorWhenMoveFails(): void
    {
        $imageStub = $this->createImageStub();
        $imageCollectionSpy = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionSpy
            ->method('getAll')
            ->willReturn([
                uniqid() => $imageStub
            ]);

        $fileSystemUtilsSpy = $this->createMock(FileSystemUtilsInterface::class);
        $fileSystemUtilsSpy->method('moveFile')
            ->willThrowException(new FileSystemException());

        $loggerSpy = $this->createMock(PsrLoggerInterface::class);
        $loggerSpy
            ->method('error')
            ->with($this->stringStartsWith('Failed to move image:'));

        $sut = $this->getSut(
            fileSystemUtils: $fileSystemUtilsSpy,
            logger: $loggerSpy
        );

        $sut->moveImages(images: $imageCollectionSpy, destination: uniqid());

        $this->assertTrue(true);
    }

    #[Test]
    public function itDeletesImagesSuccessfully(): void
    {
        $sourcePath = uniqid();
        $fileName = uniqid();

        $imageMock = $this->createImageStub(imageName: $fileName, directory: $sourcePath);
        $imageCollectionSpy = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionSpy
            ->method('getAll')
            ->willReturn([
                uniqid() => $imageMock
            ]);

        $fileSystemUtilsSpy = $this->createMock(FileSystemUtilsInterface::class);
        $fileSystemUtilsSpy->expects($this->once())
            ->method('deleteFile')
            ->with($sourcePath . '/' . $fileName);

        $loggerSpy = $this->createMock(PsrLoggerInterface::class);
        $loggerSpy
            ->method('info')
            ->with(self::stringContains('Deleted image:'));

        $sut = $this->getSut(
            fileSystemUtils: $fileSystemUtilsSpy,
            logger: $loggerSpy
        );

        $sut->deleteImages($imageCollectionSpy);

        $this->assertTrue(true);
    }

    #[Test]
    public function itLogsErrorWhenDeleteFails(): void
    {
        $sourcePath = uniqid();
        $fileName = uniqid();

        $imageMock = $this->createImageStub(imageName: $fileName, directory: $sourcePath);
        $imageCollectionSpy = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionSpy
            ->method('getAll')
            ->willReturn([
                uniqid() => $imageMock
            ]);

        $fileSystemUtilsSpy = $this->createMock(FileSystemUtilsInterface::class);
        $fileSystemUtilsSpy->expects($this->once())
            ->method('deleteFile')
            ->willThrowException(new FileSystemException());

        $loggerSpy = $this->createMock(PsrLoggerInterface::class);
        $loggerSpy
            ->method('error')
            ->with($this->stringContains('Failed to delete image'));

        $sut = $this->getSut(
            fileSystemUtils: $fileSystemUtilsSpy,
            logger: $loggerSpy
        );

        $sut->deleteImages($imageCollectionSpy);

        $this->assertTrue(true);
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
    ): ImageManagerService {
        $fileSystemUtils ??= $this->createMock(FileSystemUtilsInterface::class);
        $logger ??= $this->createMock(PsrLoggerInterface::class);

        return new ImageManagerService(
            fileSystemUtils: $fileSystemUtils,
            logger: $logger
        );
    }
}
