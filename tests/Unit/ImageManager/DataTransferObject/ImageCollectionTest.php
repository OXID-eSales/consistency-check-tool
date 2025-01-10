<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace ImageManager\DataTransferObject;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataType;
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

        $this->assertCount(1, $sut->getAll());
        $this->assertSame($image, $sut->getAll()[0]);
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

        $allImages = $sut->getAll();

        $this->assertSame([$image1, $image2, $image3], $allImages);
    }

    private function getSut(): ImageCollectionInterface
    {
        return new ImageCollection();
    }
}
