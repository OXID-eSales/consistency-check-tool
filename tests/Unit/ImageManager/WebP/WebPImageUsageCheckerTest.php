<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\WebP;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageUsageCheckerInterface;
use OxidEsales\ConsistencyCheck\ImageManager\WebP\WebPImageUsageChecker;
use OxidEsales\Eshop\Core\Config;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WebPImageUsageCheckerTest extends TestCase
{
    #[Test]
    public function itDefersToInnerCheckerForNonWebpImages(): void
    {
        $imageDataTypeStub = $this->getImageDataTypeStub();

        $usedImageCollectionStub = $this->createStub(ImageCollectionInterface::class);

        $imageUsageCheckerMock = $this->createMock(ImageUsageCheckerInterface::class);
        $imageUsageCheckerMock->expects($this->once())
            ->method('isUsed')
            ->with($imageDataTypeStub, $usedImageCollectionStub)
            ->willReturn(true);

        $sut = $this->getSut(
            originalChecker: $imageUsageCheckerMock
        );

        $this->assertTrue($sut->isUsed($imageDataTypeStub, $usedImageCollectionStub));
    }

    #[Test]
    public function itReturnsFalseIfWebpIsDisabled(): void
    {
        $imageDataTypeStub = $this->getImageDataTypeStub(imageName: uniqid() . '.webp');

        $usedImageCollectionStub = $this->createStub(ImageCollectionInterface::class);

        $imageUsageCheckerMock = $this->createMock(ImageUsageCheckerInterface::class);
        $imageUsageCheckerMock->expects($this->once())
            ->method('isUsed')
            ->with($imageDataTypeStub, $usedImageCollectionStub)
            ->willReturn(false);

        $configMock = $this->createStub(Config::class);
        $configMock->method('getConfigParam')->with('blConvertImagesToWebP')->willReturn(false);

        $sut = $this->getSut(
            originalChecker: $imageUsageCheckerMock,
            config: $configMock
        );
        $this->assertFalse($sut->isUsed($imageDataTypeStub, $usedImageCollectionStub));
    }

    #[Test]
    public function itChecksBaseImageIfWebpIsEnabled(): void
    {
        $fieldName = uniqid();
        $directory = uniqid();
        $baseName = uniqid();
        $webpName = $baseName . '.webp';

        $imageDataTypeStub = $this->getImageDataTypeStub(
            imageName: $webpName,
            fieldName: $fieldName,
            directory: $directory
        );

        $usedImageCollectionStub = $this->createStub(ImageCollectionInterface::class);

        $originalChecker = $this->createMock(ImageUsageCheckerInterface::class);
        $originalChecker->method('isUsed')
            ->willReturnCallback(function (ImageDataTypeInterface $image) use ($baseName) {
                return $image->getImageName() === $baseName;
            });

        $configMock = $this->createStub(Config::class);
        $configMock->method('getConfigParam')->with('blConvertImagesToWebP')->willReturn(true);

        $sut = $this->getSut(
            originalChecker: $originalChecker,
            config: $configMock
        );

        $this->assertTrue($sut->isUsed($imageDataTypeStub, $usedImageCollectionStub));
    }

    #[Test]
    public function itReturnsTrueIfWebpImageIsExplicitlyUsed()
    {
        $imageDataTypeStub = $this->getImageDataTypeStub();
        $usedImageCollectionStub = $this->createStub(ImageCollectionInterface::class);

        $originalCheckerMock = $this->createMock(ImageUsageCheckerInterface::class);
        $originalCheckerMock->expects($this->once())
            ->method('isUsed')
            ->with($imageDataTypeStub, $usedImageCollectionStub)
            ->willReturn(true);

        $sut = $this->getSut(originalChecker: $originalCheckerMock);

        $this->assertTrue($sut->isUsed($imageDataTypeStub, $usedImageCollectionStub));
    }

    private function getImageDataTypeStub(
        ?string $imageName = null,
        ?string $fieldName = null,
        ?string $directory = null
    ): ImageDataTypeInterface {
        return $this->createConfiguredStub(ImageDataTypeInterface::class, [
            'getImageName' => $imageName ?? uniqid(),
            'getFieldName' => $fieldName ?? uniqid(),
            'getDirectory' => $directory ?? uniqid(),
        ]);
    }

    private function getSut(
        ?ImageUsageCheckerInterface $originalChecker = null,
        ?Config $config = null,
    ): ImageUsageCheckerInterface {
        return new WebPImageUsageChecker(
            originalChecker: $originalChecker ?? $this->createStub(ImageUsageCheckerInterface::class),
            config: $config ?? $this->createStub(Config::class)
        );
    }
}
