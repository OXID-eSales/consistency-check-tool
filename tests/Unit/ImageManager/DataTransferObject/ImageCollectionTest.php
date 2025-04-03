<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace ImageManager\DataTransferObject;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataType;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\Eshop\Core\Config;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollection;

class ImageCollectionTest extends TestCase
{
    #[Test]
    public function getAllInitiallyReturnsEmptyArray(): void
    {
        $sut = $this->getSut();

        $this->assertEmpty($sut->getAll());
    }

    #[Test]
    public function addSingleImageToCollection(): void
    {
        $sut = $this->getSut();

        $image = new ImageDataType(uniqid(), uniqid(), uniqid());
        $sut->add($image);

        $allImages = array_values($sut->getAll());
        $this->assertCount(1, $allImages);
        $this->assertSame($image, $allImages[0]);
    }

    #[Test]
    public function addMultipleImages(): void
    {
        $sut = $this->getSut();

        $image1 = new ImageDataType(uniqid(), uniqid(), uniqid());
        $image2 = new ImageDataType(uniqid(), uniqid(), uniqid());
        $image3 = new ImageDataType(uniqid(), uniqid(), uniqid());

        $sut->add($image1);
        $sut->add($image2);
        $sut->add($image3);

        $allImages = array_values($sut->getAll());
        $this->assertCount(3, $allImages);
        $this->assertSame([$image1, $image2, $image3], $allImages);
    }

    #[Test]
    public function containsReturnsTrueForExistingImage(): void
    {
        $sut = $this->getSut();
        $image = new ImageDataType(uniqid(), uniqid(), uniqid());

        $sut->add($image);

        $this->assertTrue($sut->contains($image));
    }

    #[Test]
    public function containsReturnsFalseForNonExistingImage(): void
    {
        $sut = $this->getSut();
        $image1 = new ImageDataType(uniqid(), uniqid(), uniqid());
        $image2 = new ImageDataType(uniqid(), uniqid(), uniqid());

        $sut->add($image1);

        $this->assertFalse($sut->contains($image2));
    }

    #[Test]
    public function containsOriginalForWebpReturnsTrueIfWebpEnabledAndOriginalExists(): void
    {
        $configMock = $this->createConfigMock(true);

        $originalImage = new ImageDataType(
            $fieldName = uniqid(),
            $originalName = uniqid(),
            $directory = uniqid()
        );

        $sut = $this->getSut($configMock);
        $sut->add($originalImage);

        $webpImageStub = $this->createConfiguredStub(ImageDataTypeInterface::class, [
            'getImageName' => $originalName . '.webp',
            'getFieldName' => $fieldName,
            'getDirectory' => $directory,
        ]);

        $this->assertTrue($sut->containsOriginalForWebP($webpImageStub));
    }

    #[Test]
    public function containsOriginalForWebpReturnsFalseIfWebpEnabledButImageDoesNotEndWithWebp(): void
    {
        $configMock = $this->createConfigMock(true);

        $imageStub = $this->createStub(ImageDataTypeInterface::class);
        $imageStub->method('getImageName')->willReturn(uniqid());

        $sut = $this->getMockBuilder(ImageCollection::class)
            ->setConstructorArgs([$configMock])
            ->onlyMethods(['contains'])
            ->getMock();

        $sut->expects($this->never())->method('contains');

        $this->assertFalse($sut->containsOriginalForWebP($imageStub));
    }

    #[Test]
    public function containsOriginalForWebpReturnsFalseIfWebpDisabledEvenIfImageEndsWithWebp(): void
    {
        $configMock = $this->createConfigMock(false);

        $imageStub = $this->createStub(ImageDataTypeInterface::class);
        $imageStub->method('getImageName')->willReturn(uniqid() . '.webp');

        $sut = $this->getMockBuilder(ImageCollection::class)
            ->setConstructorArgs([$configMock])
            ->onlyMethods(['contains'])
            ->getMock();

        $sut->expects($this->never())->method('contains');

        $this->assertFalse($sut->containsOriginalForWebP($imageStub));
    }

    private function createConfigMock(bool $webpEnabled): Config
    {
        $configMock = $this->createMock(Config::class);
        $configMock->method('getConfigParam')
            ->with('blConvertImagesToWebP')
            ->willReturn($webpEnabled ? 1 : 0);

        return $configMock;
    }

    private function getSut(
        Config $config = null,
    ): ImageCollectionInterface {
        return new ImageCollection($config ?? $this->createStub(Config::class));
    }
}
