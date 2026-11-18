<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;

interface SeoUrlRepositoryInterface
{
    /**
     * @return array<SeoUrlDtoInterface>
     */
    public function findUnusedUrls(): array;

    /**
     * @return array<SeoUrlDtoInterface>
     */
    public function findDuplicateUrls(string $suffix): array;

    /**
     * Product SEO URLs saved for a category, vendor or manufacturer (OXPARAMS),
     * whose standard URL lost that context.
     *
     * @return array<SeoUrlDtoInterface>
     */
    public function findProductUrlsWithoutContext(): array;

    /**
     * @param array<SeoUrlDtoInterface> $seoUrlDtos
     */
    public function deleteUrls(array $seoUrlDtos): int;
}
