<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Entity;

class ImageEntity implements EntityInterface
{
    public function __construct(
        private readonly string $table,
        private readonly string $fieldName,
        private readonly string $directory,
    ) {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
