<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;

class ImageEntityFilterService implements ImageEntityFilterServiceInterface
{
    public function filterEntitiesByName(iterable $entities, ?string $name = null): array
    {
        return array_filter(iterator_to_array($entities), function (ImageEntityInterface $entity) use ($name) {
            return !$name || strtolower($entity->getName()) === strtolower($name);
        });
    }
}
