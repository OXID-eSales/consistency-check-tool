<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Factory;

use League\Csv\Reader;

interface CsvReaderFactoryInterface
{
    /**
     * @return Reader<array<string, string>>
     */
    public function create(string $path): Reader;
}
