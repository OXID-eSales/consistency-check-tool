<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Factory;

use League\Csv\Reader;

interface CsvReaderFactoryInterface
{
    /** @phpstan-ignore missingType.generics */
    public function create(string $path): Reader;
}
