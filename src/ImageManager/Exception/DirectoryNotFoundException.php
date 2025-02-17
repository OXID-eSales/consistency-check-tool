<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Exception;

class DirectoryNotFoundException extends \RuntimeException
{
    public function __construct(string $directoryPath)
    {
        $message = sprintf(
            'Directory not found: %s',
            $directoryPath,
        );

        parent::__construct($message);
    }
}
