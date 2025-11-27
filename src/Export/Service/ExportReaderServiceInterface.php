<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Service;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportReaderConfigurationInterface;
use OxidEsales\ConsistencyCheck\Shared\Dto\ExportableDtoInterface;

interface ExportReaderServiceInterface
{
    /**
     * @return array<ExportableDtoInterface>
     */
    public function read(ExportReaderConfigurationInterface $configuration): array;
}
