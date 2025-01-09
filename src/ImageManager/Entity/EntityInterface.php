<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Entity;

interface EntityInterface
{
    public function getTable(): string;
    public function getColumn(): string;
    public function getDirectory(): string;
}
