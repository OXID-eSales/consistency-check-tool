<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Service;

use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\FilterInterface;

interface FilterRegistryInterface
{
    public function get(string $name): FilterInterface;

    /**
     * @return array<string>
     */
    public function getAvailableFilters(): array;
}
