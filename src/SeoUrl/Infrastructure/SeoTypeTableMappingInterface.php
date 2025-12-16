<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure;

interface SeoTypeTableMappingInterface
{
    public function getSeoType(): string;

    public function getReferenceTable(): string;
}
