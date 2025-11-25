<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Factory;

use League\Csv\Writer;

final class CsvWriterFactory implements CsvWriterFactoryInterface
{
    public function create(string $path): Writer
    {
        return Writer::from($path, 'w');
    }
}
