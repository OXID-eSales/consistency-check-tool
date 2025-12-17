<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Exception;

use Exception;
use Throwable;

final class CsvReadException extends Exception
{
    public function __construct(string $filepath, Throwable $previous)
    {
        $message = sprintf(
            'Cannot read CSV file: %s. Error: %s',
            $filepath,
            $previous->getMessage()
        );

        parent::__construct(message: $message, previous:  $previous);
    }
}
