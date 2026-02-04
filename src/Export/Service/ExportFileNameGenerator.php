<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Service;

use OxidEsales\ConsistencyCheck\Export\Exception\ExportDirectoryNotFoundException;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;

class ExportFileNameGenerator implements ExportFileNameGeneratorInterface
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly string $exportDirectoryPath,
    ) {
    }

    public function generate(string $filePrefix, string $fileExtension): string
    {
        $absoluteExportDirectoryPath = $this->pathResolver->getAbsolutePath($this->exportDirectoryPath);

        if (!is_dir($absoluteExportDirectoryPath)) {
            throw new ExportDirectoryNotFoundException($absoluteExportDirectoryPath);
        }

        $timestamp = date('Y-m-d_H-i-s');

        return sprintf(
            '%s/%s-%s.%s',
            $absoluteExportDirectoryPath,
            $filePrefix,
            $timestamp,
            $fileExtension
        );
    }
}
