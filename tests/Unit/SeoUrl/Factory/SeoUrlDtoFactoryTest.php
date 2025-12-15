<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Factory;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDto;
use OxidEsales\ConsistencyCheck\SeoUrl\Factory\SeoUrlDtoFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoUrlDtoFactoryTest extends TestCase
{
    #[Test]
    public function createFromArray(): void
    {
        $shopId = rand();
        $langId = rand();
        $fixed = rand(0, 1);
        $expired = rand(0, 1);
        $data = [
            'OXOBJECTID' => $objectId = uniqid(),
            'OXIDENT' => $ident = uniqid(),
            'OXSHOPID' => (string)$shopId,
            'OXLANG' => (string)$langId,
            'OXSTDURL' => $stdUrl = uniqid(),
            'OXSEOURL' => $seoUrl = uniqid(),
            'OXTYPE' => $type = uniqid(),
            'OXFIXED' => (string)$fixed,
            'OXEXPIRED' => (string)$expired,
            'OXPARAMS' => $params = uniqid(),
            'OXTIMESTAMP' => $timestamp = uniqid(),
        ];

        $sut = new SeoUrlDtoFactory();
        $result = $sut->createFromArray($data);

        $this->assertInstanceOf(SeoUrlDto::class, $result);
        $this->assertSame($objectId, $result->getObjectId());
        $this->assertSame($ident, $result->getIdent());
        $this->assertSame($shopId, $result->getShopId());
        $this->assertSame($langId, $result->getLanguageId());
        $this->assertSame($stdUrl, $result->getStdUrl());
        $this->assertSame($seoUrl, $result->getSeoUrl());
        $this->assertSame($type, $result->getType());
        $this->assertSame((bool)$fixed, $result->getFixed());
        $this->assertSame((bool)$expired, $result->getExpired());
        $this->assertSame($params, $result->getParams());
        $this->assertSame($timestamp, $result->getTimestamp());
    }
}
