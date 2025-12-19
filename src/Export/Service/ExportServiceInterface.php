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
    /**
     * Exports data to a file based on the provided configuration.
     *
     * @return string The absolute path to the exported file
     */
    public function export(ExportConfigurationInterface $configuration): string;
}
