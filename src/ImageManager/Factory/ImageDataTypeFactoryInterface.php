<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Factory;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;

interface ImageDataTypeFactoryInterface
{
    public function createFromFileDetails(
        string $fieldName,
        string $imageName,
        string $directory,
    ): ImageDataTypeInterface;
}
