<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Repository;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoTypeTableMappingInterface;

interface SeoUrlRepositoryInterface
{
    /**
     * @return array<SeoUrlDtoInterface>
     */
    public function findUnusedUrls(SeoTypeTableMappingInterface $mapping): array;

    /**
     * @return array<SeoUrlDtoInterface>
     */
    public function findDuplicateUrls(string $suffix): array;

    /**
     * @param array<string> $oxids
     */
    public function deleteUrls(array $oxids): int;
}
