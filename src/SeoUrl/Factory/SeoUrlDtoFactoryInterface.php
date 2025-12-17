<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Factory;

use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;

/**
 * @phpstan-type SeoUrlTableRow array{
 *     OXOBJECTID: string,
 *     OXIDENT: string,
 *     OXSHOPID: string,
 *     OXLANG: string,
 *     OXSTDURL: string,
 *     OXSEOURL: string,
 *     OXTYPE: string,
 *     OXFIXED: string,
 *     OXEXPIRED: string,
 *     OXPARAMS: string,
 *     OXTIMESTAMP: string
 * }
 */
interface SeoUrlDtoFactoryInterface extends DtoFactoryInterface
{
    /**
     * @param SeoUrlTableRow $data
     */
    public function createFromArray(array $data): SeoUrlDtoInterface;
}
