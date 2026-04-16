<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Configuration;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;

final class ExportConfiguration implements ExportConfigurationInterface
{
    /**
     * @param array<ExportableDtoInterface> $items
     * @param array<string> $headers
     */
    public function __construct(
        private readonly array $items,
        private readonly array $headers,
        private readonly ArrayFactoryInterface $arrayFactory,
        private readonly string $filePrefix,
    ) {
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getArrayFactory(): ArrayFactoryInterface
    {
        return $this->arrayFactory;
    }

    public function getFilePrefix(): string
    {
        return $this->filePrefix;
    }
}
