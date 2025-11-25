<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Service;

use OxidEsales\ConsistencyCheck\Shared\Exception\FileNotFoundException;
use OxidEsales\ConsistencyCheck\Shared\Exception\InvalidFileFormatException;
use OxidEsales\ConsistencyCheck\Shared\Mapper\CsvMapperInterface;

interface CsvReaderServiceInterface
{
    /**
     * @return array<object>
     * @throws FileNotFoundException | InvalidFileFormatException
     */
    public function readFromCsv(string $filepath, CsvMapperInterface $mapper): array;
}
