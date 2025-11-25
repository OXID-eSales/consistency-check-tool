<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Mapper;

use OxidEsales\ConsistencyCheck\Shared\Mapper\CsvMapperInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\DTO\SeoUrlDto;
use OxidEsales\ConsistencyCheck\SeoUrl\DTO\SeoUrlDtoInterface;

final class SeoUrlCsvMapper implements CsvMapperInterface
{
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

    public function toArray(object $dto): array
    {
        assert($dto instanceof SeoUrlDtoInterface, 'DTO must implement SeoUrlDtoInterface');

        return [
            'OXOBJECTID' => $dto->getObjectId(),
            'OXIDENT' => $dto->getIdent(),
            'OXSHOPID' => (string)$dto->getShopId(),
            'OXLANG' => (string)$dto->getLanguageId(),
            'OXSTDURL' => $dto->getStdUrl(),
            'OXSEOURL' => $dto->getSeoUrl(),
            'OXTYPE' => $dto->getType(),
            'OXFIXED' => (string)$dto->getFixed(),
            'OXEXPIRED' => (string)$dto->getExpired(),
            'OXPARAMS' => $dto->getParams(),
            'OXTIMESTAMP' => $dto->getTimestamp(),
        ];
    }

    public function fromArray(array $data): object
    {
        return new SeoUrlDto(
            objectId: (string)($data['OXOBJECTID'] ?? ''),
            ident: (string)($data['OXIDENT'] ?? ''),
            shopId: (int)($data['OXSHOPID'] ?? 0),
            languageId: (int)($data['OXLANG'] ?? 0),
            stdUrl: (string)($data['OXSTDURL'] ?? ''),
            seoUrl: (string)($data['OXSEOURL'] ?? ''),
            type: (string)($data['OXTYPE'] ?? ''),
            fixed: (int)($data['OXFIXED'] ?? 0),
            expired: (int)($data['OXEXPIRED'] ?? 0),
            params: (string)($data['OXPARAMS'] ?? ''),
            timestamp: (string)($data['OXTIMESTAMP'] ?? ''),
        );
    }
}
