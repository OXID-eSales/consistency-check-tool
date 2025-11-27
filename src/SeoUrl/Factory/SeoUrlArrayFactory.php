<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Factory;

use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\Shared\Dto\ExportableDtoInterface;

final class SeoUrlArrayFactory implements ArrayFactoryInterface
{
    public function createFromDto(ExportableDtoInterface $dto): array
    {
        if (!$dto instanceof SeoUrlDtoInterface) {
            throw new \InvalidArgumentException('Expected SeoUrlDtoInterface');
        }

        return [
            'OXOBJECTID' => $dto->getObjectId(),
            'OXIDENT' => $dto->getIdent(),
            'OXSHOPID' => $dto->getShopId(),
            'OXLANG' => $dto->getLanguageId(),
            'OXSTDURL' => $dto->getStdUrl(),
            'OXSEOURL' => $dto->getSeoUrl(),
            'OXTYPE' => $dto->getType(),
            'OXFIXED' => $dto->getFixed(),
            'OXEXPIRED' => $dto->getExpired(),
            'OXPARAMS' => $dto->getParams(),
            'OXTIMESTAMP' => $dto->getTimestamp(),
        ];
    }
}
