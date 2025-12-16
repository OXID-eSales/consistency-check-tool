<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure;

final class SeoTypeTableMapping implements SeoTypeTableMappingInterface
{
    public function __construct(
        private readonly string $seoType,
        private readonly string $referenceTable,
    ) {
    }

    public function getSeoType(): string
    {
        return $this->seoType;
    }

    public function getReferenceTable(): string
    {
        return $this->referenceTable;
    }
}
