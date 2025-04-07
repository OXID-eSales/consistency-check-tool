<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

class FileLogReader implements LogReaderInterface
{
    public function __construct(
        private readonly string $logFilePath
    ) {
    }

    /**
     * @inheritDoc
     */
    public function readLines(): array
    {
        if (!file_exists($this->logFilePath)) {
            return [];
        }

        return file($this->logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    }
}
