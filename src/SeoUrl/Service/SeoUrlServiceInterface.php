<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Service;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;

interface SeoUrlServiceInterface
{
    /**
     * @return array<SeoUrlDtoInterface>
     */
    public function findUnusedUrls(): array;

    /**
     * @return array<SeoUrlDtoInterface>
     */
    public function findDuplicateUrls(?string $suffix = null): array;

    /**
     * @param array<string> $oxids
     */
    public function deleteUrls(array $oxids): int;
}
