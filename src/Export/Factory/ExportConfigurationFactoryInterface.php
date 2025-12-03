<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Factory;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Shared\Dto\ExportableDtoInterface;

interface ExportConfigurationFactoryInterface
{
    /**
     * @param array<ExportableDtoInterface> $items
     */
    public function create(array $items, string $filePath): ExportConfigurationInterface;
}
