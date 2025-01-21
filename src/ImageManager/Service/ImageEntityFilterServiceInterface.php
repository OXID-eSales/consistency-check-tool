<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

interface ImageEntityFilterServiceInterface
{
    public function filterEntitiesByName(iterable $entities, ?string $name = null): array;
}
