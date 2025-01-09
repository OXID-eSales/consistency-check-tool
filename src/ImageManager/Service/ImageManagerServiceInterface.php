<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;

interface ImageManagerServiceInterface
{
    public function moveImages(ImageCollectionInterface $unattachedImages, string $destination): void;
    public function deleteImages(ImageCollectionInterface $unattachedImages): void;
}
