<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;

class ImageCollection implements ImageCollectionInterface
{
    /**
     * @var array<ImageDataTypeInterface>
     */
    private array $data = [];

    /**
     * @inheritDoc
     */
    public function add(ImageDataTypeInterface $image): void
    {
        $this->data[] = $image;
    }

    /**
     * @inheritDoc
     */
    public function getAll(): array
    {
        return $this->data;
    }
}
