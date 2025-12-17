<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Configuration;

use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;

interface ExportReaderConfigurationInterface
{
    public function getFilePath(): string;

    public function getDtoFactory(): DtoFactoryInterface;
}
