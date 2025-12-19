<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ImageManager\Dto;

use OxidEsales\ConsistencyCheck\ImageManager\Dto\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use OxidEsales\ConsistencyCheck\ImageManager\Dto\ImageCollection;

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

    private function getSut(): ImageCollectionInterface
    {
        return new ImageCollection();
    }
}
