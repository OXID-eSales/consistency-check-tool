<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\WebP;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\ConsistencyCheck\ImageManager\WebP\WebPImageUsageChecker;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageUsageCheckerInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;

final class WebPImageUsageCheckerTest extends TestCase
{
    #[Test]
    public function itDefersToInnerCheckerForNonWebpImages(): void
    {
        $imageDataTypeStub = $this->createStub(ImageDataTypeInterface::class);
        $imageDataTypeStub->method('getImageName')->willReturn(uniqid());

        $usedImageCollectionStub = $this->createStub(ImageCollectionInterface::class);

        $imageUsageCheckerMock = $this->createMock(ImageUsageCheckerInterface::class);
        $imageUsageCheckerMock->expects($this->once())
            ->method('isUsed')
            ->with($imageDataTypeStub, $usedImageCollectionStub)
            ->willReturn(true);

        $sut = $this->getSut(
            imageUsageChecker: $imageUsageCheckerMock
        );

        $this->assertTrue($sut->isUsed($imageDataTypeStub, $usedImageCollectionStub));
    }

    #[Test]
    public function itReturnsFalseIfWebpIsDisabled(): void
    {
        $imageDataTypeStub = $this->createStub(ImageDataTypeInterface::class);
        $imageDataTypeStub->method('getImageName')->willReturn(uniqid() . '.webp');

        $usedImageCollectionStub = $this->createStub(ImageCollectionInterface::class);

        $imageUsageCheckerStub = $this->createMock(ImageUsageCheckerInterface::class);
        $imageUsageCheckerStub->expects($this->never())->method('isUsed');

        $configMock = $this->createStub(Config::class);
        $configMock->method('getConfigParam')->with('blConvertImagesToWebP')->willReturn(false);

        $sut = $this->getSut(
            imageUsageChecker: $imageUsageCheckerStub,
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

        $imageDataTypeStub = $this->createStub(ImageDataTypeInterface::class);
        $imageDataTypeStub->method('getImageName')->willReturn($webpName);
        $imageDataTypeStub->method('getFieldName')->willReturn($fieldName);
        $imageDataTypeStub->method('getDirectory')->willReturn($directory);

        $usedImageCollectionStub = $this->createStub(ImageCollectionInterface::class);

        $imageUsageCheckerMock = $this->createMock(ImageUsageCheckerInterface::class);
        $imageUsageCheckerMock->expects($this->once())
            ->method('isUsed')
            ->with($this->callback(
                function (ImageDataTypeInterface $baseImage) use ($baseName, $directory, $fieldName) {
                    return $baseImage->getImageName() === $baseName
                    && $baseImage->getDirectory() === $directory
                    && $baseImage->getFieldName() === $fieldName;
                }
            ), $usedImageCollectionStub)
            ->willReturn(true);

        $configMock = $this->createStub(Config::class);
        $configMock->method('getConfigParam')->with('blConvertImagesToWebP')->willReturn(true);

        $sut = $this->getSut(
            imageUsageChecker: $imageUsageCheckerMock,
            config: $configMock
        );

        $this->assertTrue($sut->isUsed($imageDataTypeStub, $usedImageCollectionStub));
    }

    private function getSut(
        ?ImageUsageCheckerInterface $imageUsageChecker = null,
        ?Config $config = null,
    ): ImageUsageCheckerInterface {
        return new WebPImageUsageChecker(
            $imageUsageChecker ?? $this->createStub(ImageUsageCheckerInterface::class),
            $config ?? $this->createStub(Config::class)
        );
    }
}
