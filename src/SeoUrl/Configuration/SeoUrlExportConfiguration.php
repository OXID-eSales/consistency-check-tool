<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Configuration;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;

final class SeoUrlExportConfiguration implements ExportConfigurationInterface
{
    public function __construct(
        private readonly array $items,
        private readonly ArrayFactoryInterface $arrayFactory,
        private readonly string $filePath,
    ) {
    }

    public function getHeaders(): array
    {
        return [
            'OXOBJECTID',
            'OXIDENT',
            'OXSHOPID',
            'OXLANG',
            'OXSTDURL',
            'OXSEOURL',
            'OXTYPE',
            'OXFIXED',
            'OXEXPIRED',
            'OXPARAMS',
            'OXTIMESTAMP',
        ];
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getArrayFactory(): ArrayFactoryInterface
    {
        return $this->arrayFactory;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
