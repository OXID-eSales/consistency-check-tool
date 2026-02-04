<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;

class FileLogReader implements LogReaderInterface
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly string $logFilePath,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function readLines(): array
    {
        $absoluteFileLogPath = $this->pathResolver->getAbsolutePath($this->logFilePath);

        if (!file_exists($absoluteFileLogPath)) {
            return [];
        }

        return file($absoluteFileLogPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    }
}
