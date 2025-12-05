<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Dto;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDto;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoUrlDtoTest extends TestCase
{
    #[Test]
    public function itReturnsCorrectGetterValues(): void
    {
        $sut = new SeoUrlDto(
            objectId: $expectedObjectId = uniqid(),
            ident: $expectedIdent = uniqid(),
            shopId: $expectedShopId = rand(1, 10),
            languageId: $expectedLanguageId = rand(0, 5),
            stdUrl: $expectedStdUrl = uniqid(),
            seoUrl: $expectedSeoUrl = uniqid() . '.html',
            type: $expectedType = uniqid(),
            fixed: $expectedFixed = rand(0, 1),
            expired: $expectedExpired = rand(0, 1),
            params: $expectedParams = uniqid(),
            timestamp: $expectedTimestamp = date('Y-m-d H:i:s'),
        );

        $this->assertSame($expectedObjectId, $sut->getObjectId());
        $this->assertSame($expectedIdent, $sut->getIdent());
        $this->assertSame($expectedShopId, $sut->getShopId());
        $this->assertSame($expectedLanguageId, $sut->getLanguageId());
        $this->assertSame($expectedStdUrl, $sut->getStdUrl());
        $this->assertSame($expectedSeoUrl, $sut->getSeoUrl());
        $this->assertSame($expectedType, $sut->getType());
        $this->assertSame($expectedFixed, $sut->getFixed());
        $this->assertSame($expectedExpired, $sut->getExpired());
        $this->assertSame($expectedParams, $sut->getParams());
        $this->assertSame($expectedTimestamp, $sut->getTimestamp());
    }
}
