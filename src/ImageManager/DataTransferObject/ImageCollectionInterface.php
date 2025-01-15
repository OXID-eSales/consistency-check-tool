<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;

interface ImageCollectionInterface
{
    public function add(ImageDataTypeInterface $image): void;

    /**
     * @return array<ImageDataTypeInterface>
     */
    public function getAll(): array;
}
