<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\DTO;

final class SeoUrlDto implements SeoUrlDtoInterface
{
    public function __construct(
        private readonly string $objectId,
        private readonly string $ident,
        private readonly int $shopId,
        private readonly int $languageId,
        private readonly string $stdUrl,
        private readonly string $seoUrl,
        private readonly string $type,
        private readonly int $fixed,
        private readonly int $expired,
        private readonly string $params,
        private readonly string $timestamp,
    ) {
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function getIdent(): string
    {
        return $this->ident;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function getStdUrl(): string
    {
        return $this->stdUrl;
    }

    public function getSeoUrl(): string
    {
        return $this->seoUrl;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFixed(): int
    {
        return $this->fixed;
    }

    public function getExpired(): int
    {
        return $this->expired;
    }

    public function getParams(): string
    {
        return $this->params;
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }
}
