<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Repository;

use OxidEsales\ConsistencyCheck\ImageManager\DataType\ImageDataTypeInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Exception\ImageDatabaseRepositoryException;

interface ImageDatabaseRepositoryInterface
{
    /**
     * @return array<ImageDataTypeInterface>
     * @throws ImageDatabaseRepositoryException
     */
    public function getImages(ImageEntityInterface $entity): array;
}
