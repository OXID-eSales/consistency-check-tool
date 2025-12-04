<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Factory;

use OxidEsales\ConsistencyCheck\ImageManager\Dto\ImageCollectionInterface;

interface ImageCollectionFactoryInterface
{
    public function create(): ImageCollectionInterface;
}
