<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Configuration;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportReaderConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Factory\SeoUrlDtoFactoryInterface;

final class SeoUrlExportReaderConfiguration implements ExportReaderConfigurationInterface
{
    public function __construct(
        private readonly string $filePath,
        private readonly SeoUrlDtoFactoryInterface $dtoFactory,
    ) {
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getDtoFactory(): DtoFactoryInterface
    {
        return $this->dtoFactory;
    }
}
