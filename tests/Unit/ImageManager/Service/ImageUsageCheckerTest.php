<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageUsageChecker;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ImageUsageCheckerTest extends TestCase
{
    #[Test]
    public function itReturnsTrueIfImageIsInCollection(): void
    {
        $imageDataTypeStub = $this->createMock(ImageDataTypeInterface::class);
        $imageCollectionMock = $this->createMock(ImageCollectionInterface::class);
        $imageCollectionMock->method('contains')->with($imageDataTypeStub)->willReturn(true);

        $sut = new ImageUsageChecker();
        $result = $sut->isUsed(
            image: $imageDataTypeStub,
            usedImages: $imageCollectionMock
        );
        $this->assertTrue($result);
    }

    #[Test]
    public function itReturnsFalseIfImageIsNotInCollection(): void
    {
        $image = $this->createStub(ImageDataTypeInterface::class);
        $collection = $this->createMock(ImageCollectionInterface::class);
        $collection->method('contains')->with($image)->willReturn(false);

        $sut = new ImageUsageChecker();
        $this->assertFalse($sut->isUsed($image, $collection));
    }
}
