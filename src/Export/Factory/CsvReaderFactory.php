<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Factory;

use League\Csv\Reader;

final class CsvReaderFactory implements CsvReaderFactoryInterface
{
    /** @phpstan-ignore missingType.generics */
    public function create(string $path): Reader
    {
        return Reader::from($path, 'r');
    }
}
