<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Service;

use OxidEsales\ConsistencyCheck\Shared\Exception\InvalidFileFormatException;
use OxidEsales\ConsistencyCheck\Shared\Mapper\CsvMapperInterface;

interface CsvExportServiceInterface
{
    /**
     * @param array<object> $data
     * @throws InvalidFileFormatException
     */
    public function exportToCsv(
        array $data,
        string $filepath,
        CsvMapperInterface $mapper
    ): int;
}
