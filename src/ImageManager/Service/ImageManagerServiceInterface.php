<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\Dto\ImageCollectionInterface;

interface ImageManagerServiceInterface
{
    public function moveImages(ImageCollectionInterface $images, string $destination, bool $dryRun = false): int;
    public function deleteImages(ImageCollectionInterface $images, bool $dryRun = false): int;
}
