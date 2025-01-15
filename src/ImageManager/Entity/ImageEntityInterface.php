<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Entity;

interface ImageEntityInterface
{
    public function getTable(): string;
    public function getFieldName(): string;
    public function getDirectory(): string;
}
