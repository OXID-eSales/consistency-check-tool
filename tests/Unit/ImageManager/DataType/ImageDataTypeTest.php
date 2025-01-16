<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace ImageManager\DataType;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ImageDataTypeTest extends TestCase
{
    #[Test]
    public function itReturnsCorrectGetterValues(): void
    {
        $sut = new ImageDataType(
            fieldName: $expectedFieldName = uniqid(),
            imageName: $expectedImageName = uniqid(),
            directory: $expectedDirectory = uniqid(),
        );

        $this->assertSame($expectedFieldName, $sut->getFieldName());
        $this->assertSame($expectedImageName, $sut->getImageName());
        $this->assertSame($expectedDirectory, $sut->getDirectory());
    }
}
