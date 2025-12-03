<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Exception;

class ExportDirectoryNotFoundException extends \RuntimeException
{
    public function __construct(string $directoryPath)
    {
        $message = sprintf(
            'Export directory does not exist: %s',
            $directoryPath,
        );

        parent::__construct($message);
    }
}
