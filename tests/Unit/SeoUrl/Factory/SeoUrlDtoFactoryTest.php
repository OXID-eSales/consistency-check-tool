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
        $data = [
            'OXOBJECTID' => $objectId = uniqid(),
            'OXIDENT' => $ident = uniqid(),
            'OXSHOPID' => $shopId = rand(),
            'OXLANG' => $langId = rand(),
            'OXSTDURL' => $stdUrl = uniqid(),
            'OXSEOURL' => $seoUrl = uniqid(),
            'OXTYPE' => $type = uniqid(),
            'OXFIXED' => $fixed = rand(),
            'OXEXPIRED' => $expired = rand(),
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
        $this->assertSame($fixed, $result->getFixed());
        $this->assertSame($expired, $result->getExpired());
        $this->assertSame($params, $result->getParams());
        $this->assertSame($timestamp, $result->getTimestamp());
    }
}
