<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Factory;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\FilterInterface;

interface ExportConfigurationFactoryInterface
{
    public function createFromFilter(FilterInterface $filter): ExportConfigurationInterface;
}
