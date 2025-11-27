<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Export\Factory;

use OxidEsales\ConsistencyCheck\Shared\Dto\ExportableDtoInterface;

interface ArrayFactoryInterface
{
    /**
     * @return array<string, mixed>
     */
    public function createFromDto(ExportableDtoInterface $dto): array;
}
