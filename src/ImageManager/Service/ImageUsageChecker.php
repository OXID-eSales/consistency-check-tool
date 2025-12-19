<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\Dto\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;

class ImageUsageChecker implements ImageUsageCheckerInterface
{
    public function isUsed(ImageDataTypeInterface $image, ImageCollectionInterface $usedImages): bool
    {
        return $usedImages->contains($image);
    }
}
