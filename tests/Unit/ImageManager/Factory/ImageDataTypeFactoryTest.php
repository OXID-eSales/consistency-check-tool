<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Factory;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ImageDataTypeFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ImageDataTypeFactoryTest extends TestCase
{
    #[Test]
    public function itCreatesImageDataTypeObject(): void
    {
        $imageDataTypeFactory1 = new ImageDataTypeFactory();
        $imageDataTypeFactory2 = new ImageDataTypeFactory();

        $imageDataType1 = $imageDataTypeFactory1->createFromFileDetails(
            $fieldName = uniqid(),
            $imageName = uniqid(),
            $directory = uniqid()
        );
        $imageDataType2 = $imageDataTypeFactory2->createFromFileDetails(uniqid(), uniqid(), uniqid());

        $this->assertNotSame($imageDataType1, $imageDataType2);

        $this->assertSame($fieldName, $imageDataType1->getFieldName());
        $this->assertSame($imageName, $imageDataType1->getImageName());
        $this->assertSame($directory, $imageDataType1->getDirectory());
    }
}
