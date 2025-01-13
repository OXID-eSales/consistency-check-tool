<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Factory;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataType;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;

class ImageDataTypeFactory implements ImageDataTypeFactoryInterface
{
    public function createFromFileDetails(
        string $fieldName,
        string $imageName,
        string $directory,
    ): ImageDataTypeInterface {
        return new ImageDataType($fieldName, $imageName, $directory);
    }
}
