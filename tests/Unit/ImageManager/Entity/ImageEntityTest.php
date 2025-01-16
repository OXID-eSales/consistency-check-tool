<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace ImageManager\Entity;

use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ImageEntityTest extends TestCase
{
    #[Test]
    public function itReturnsCorrectGetterValues()
    {
        $sut  = new ImageEntity(
            name: $expectedName = uniqid(),
            table: $expectedTable = uniqid(),
            fieldName: $expectedFieldName = uniqid(),
            directory: $expectedDirectory = uniqid(),
        );

        $this->assertSame($expectedName, $sut->getName());
        $this->assertSame($expectedTable, $sut->getTable());
        $this->assertSame($expectedFieldName, $sut->getFieldName());
        $this->assertSame($expectedDirectory, $sut->getDirectory());
    }
}
