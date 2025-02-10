<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;

interface ImageEntityFilterServiceInterface
{
    /**
     * @param ImageEntityInterface[] $entities
     * @return ImageEntityInterface[]
     */
    public function filterEntitiesByName(iterable $entities, ?string $name = null): array;
}
