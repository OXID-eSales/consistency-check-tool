<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Repository;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;

interface ImageRepositoryInterface
{
    public function getImages(ImageEntityInterface $entity): ImageCollectionInterface;
}
