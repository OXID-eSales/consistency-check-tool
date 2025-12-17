<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Factory;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDto;
use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;

final class SeoUrlDtoFactory implements SeoUrlDtoFactoryInterface
{
    public function createFromArray(array $data): SeoUrlDtoInterface
    {
        return new SeoUrlDto(
            objectId: $data['OXOBJECTID'],
            ident: $data['OXIDENT'],
            shopId: (int)$data['OXSHOPID'],
            languageId: (int)$data['OXLANG'],
            stdUrl: $data['OXSTDURL'],
            seoUrl: $data['OXSEOURL'],
            type: $data['OXTYPE'],
            fixed: (int)$data['OXFIXED'],
            expired: (int)$data['OXEXPIRED'],
            params: $data['OXPARAMS'],
            timestamp: $data['OXTIMESTAMP'],
        );
    }
}
