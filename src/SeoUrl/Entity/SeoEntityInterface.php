<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Entity;

interface SeoEntityInterface
{
    /**
     * Get SEO type (oxarticle, oxcategory, oxmanufacturer, oxvendor, oxcontent)
     */
    public function getSeoType(): string;

    public function getReferenceTable(): ?string;
}
