<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Factory;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportReaderConfigurationInterface;

/** @SuppressWarnings(PHPMD.LongClassName) */ // @phpstan-ignore-line
interface ExportReaderConfigurationFactoryInterface
{
    public function create(string $filePath): ExportReaderConfigurationInterface;
}
