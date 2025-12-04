<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Factory;

use OxidEsales\ConsistencyCheck\ImageManager\Dto\ImageCollection;
use OxidEsales\ConsistencyCheck\ImageManager\Dto\ImageCollectionInterface;
use OxidEsales\Eshop\Core\Config;

class ImageCollectionFactory implements ImageCollectionFactoryInterface
{
    public function create(): ImageCollectionInterface
    {
        return new ImageCollection();
    }
}
