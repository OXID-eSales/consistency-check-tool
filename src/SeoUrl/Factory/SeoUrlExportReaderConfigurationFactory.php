<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Factory;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportReaderConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\ExportReaderConfigurationFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Configuration\SeoUrlExportReaderConfiguration;

final class SeoUrlExportReaderConfigurationFactory implements ExportReaderConfigurationFactoryInterface
{
    public function __construct(
        private readonly DtoFactoryInterface $dtoFactory
    ) {
    }

    public function create(string $filePath): ExportReaderConfigurationInterface
    {
        return new SeoUrlExportReaderConfiguration($filePath, $this->dtoFactory);
    }
}
