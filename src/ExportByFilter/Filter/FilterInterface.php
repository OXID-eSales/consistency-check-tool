<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter;

use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;

interface FilterInterface
{
    public function getName(): string;

    public function getDescription(): string;

    /**
     * @return array<string>
     */
    public function getHeaders(): array;

    public function getArrayFactory(): ArrayFactoryInterface;

    /**
     * @return array<ExportableDtoInterface>
     */
    public function getItems(): array;
}
