<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Factory;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\ExportConfigurationFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Configuration\SeoUrlExportConfiguration;

final class SeoUrlExportConfigurationFactory implements ExportConfigurationFactoryInterface
{
    public function __construct(
        private readonly ArrayFactoryInterface $arrayFactory
    ) {
    }

    public function create(array $items, string $filePath): ExportConfigurationInterface
    {
        return new SeoUrlExportConfiguration($items, $this->arrayFactory, $filePath);
    }
}
