<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Configuration;

use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;

interface ExportConfigurationInterface
{
    /**
     * @return array<string>
     */
    public function getHeaders(): array;

    /**
     * @return array<ExportableDtoInterface>
     */
    public function getItems(): array;

    public function getArrayFactory(): ArrayFactoryInterface;

    public function getFilePrefix(): string;
}
