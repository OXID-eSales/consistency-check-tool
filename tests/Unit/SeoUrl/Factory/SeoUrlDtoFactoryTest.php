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
        $objectId = uniqid();
        $ident = uniqid();
        $shopId = rand();
        $langId = rand();
        $stdUrl = uniqid();
        $seoUrl = uniqid();
        $type = uniqid();
        $fixed = rand();
        $expired = rand();
        $params = uniqid();
        $timestamp = uniqid();

        $data = [
            'OXOBJECTID' => $objectId,
            'OXIDENT' => $ident,
            'OXSHOPID' => $shopId,
            'OXLANG' => $langId,
            'OXSTDURL' => $stdUrl,
            'OXSEOURL' => $seoUrl,
            'OXTYPE' => $type,
            'OXFIXED' => $fixed,
            'OXEXPIRED' => $expired,
            'OXPARAMS' => $params,
            'OXTIMESTAMP' => $timestamp,
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
        $this->assertSame($fixed, $result->getFixed());
        $this->assertSame($expired, $result->getExpired());
        $this->assertSame($params, $result->getParams());
        $this->assertSame($timestamp, $result->getTimestamp());
    }
}
