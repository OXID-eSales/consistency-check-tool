<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Service;

interface ExportFileNameGeneratorInterface
{
    public function generate(string $filePrefix, string $fileExtension): string;
}
