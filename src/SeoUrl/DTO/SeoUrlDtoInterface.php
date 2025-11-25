<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\DTO;

interface SeoUrlDtoInterface
{
    public function getObjectId(): string;

    public function getIdent(): string;

    public function getShopId(): int;

    public function getLanguageId(): int;

    public function getStdUrl(): string;

    public function getSeoUrl(): string;

    public function getType(): string;

    public function getFixed(): int;

    public function getExpired(): int;

    public function getParams(): string;

    public function getTimestamp(): string;
}
