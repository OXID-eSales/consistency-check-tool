<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Repository;

interface ImageSourceRepositoryInterface
{
    /**
     * @return array<array<string, string|null>>
     */
    public function getAttachedImages(): array;
}
