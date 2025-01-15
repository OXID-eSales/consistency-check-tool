<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Repository;

use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;

interface ImageDatabaseRepositoryInterface
{
    /**
     * @return array<string>
     */
    public function getImages(ImageEntityInterface $entity): array;
}
