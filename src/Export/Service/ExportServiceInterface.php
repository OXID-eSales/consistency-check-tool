<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Service;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;

interface ExportServiceInterface
{
    public function export(ExportConfigurationInterface $configuration): void;
}
