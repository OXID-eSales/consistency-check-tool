<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\DataType;

class ImageDataType implements ImageDataTypeInterface
{
    public function __construct(
        private readonly string $fieldName,
        private readonly string $imageName,
        private readonly string $directory,
    ) {
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getImageName(): string
    {
        return $this->imageName;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
