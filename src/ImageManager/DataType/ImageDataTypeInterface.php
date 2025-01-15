<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\DataType;

interface ImageDataTypeInterface
{
    public function getFieldName(): string;
    public function getImageName(): string;
    public function getDirectory(): string;
}
