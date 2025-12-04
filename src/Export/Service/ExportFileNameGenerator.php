<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Service;

use OxidEsales\ConsistencyCheck\Export\Exception\ExportDirectoryNotFoundException;

class ExportFileNameGenerator implements ExportFileNameGeneratorInterface
{
    public function __construct(
        private readonly string $exportDirectoryPath,
    ) {
    }

    public function generate(string $filePrefix, string $fileExtension): string
    {
        if (!is_dir($this->exportDirectoryPath)) {
            throw new ExportDirectoryNotFoundException($this->exportDirectoryPath);
        }

        $timestamp = date('Y-m-d_H-i-s');

        return sprintf(
            '%s/%s-%s.%s',
            $this->exportDirectoryPath,
            $filePrefix,
            $timestamp,
            $fileExtension
        );
    }
}
