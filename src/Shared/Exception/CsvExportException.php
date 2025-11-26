<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Exception;

use Exception;
use Throwable;

final class CsvExportException extends Exception
{
    public function __construct(string $filepath, Throwable $previous)
    {
        $message = sprintf(
            'Cannot write CSV file: %s. Error: %s',
            $filepath,
            $previous->getMessage()
        );

        parent::__construct($message, 0, $previous);
    }
}
