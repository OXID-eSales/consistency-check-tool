<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Factory;

use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Exception\InvalidDtoTypeException;
use OxidEsales\ConsistencyCheck\SeoUrl\Factory\SeoUrlArrayFactory;
use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoUrlArrayFactoryTest extends TestCase
{
    #[Test]
    public function createFromDto(): void
    {
        $dtoStub = $this->createConfiguredStub(SeoUrlDtoInterface::class, [
            'getObjectId' => $objectId = uniqid(),
            'getIdent' => $ident = uniqid(),
            'getShopId' => $shopId = rand(),
            'getLanguageId' => $langId = rand(),
            'getStdUrl' => $stdUrl = uniqid(),
            'getSeoUrl' => $seoUrl = uniqid(),
            'getType' => $type = uniqid(),
            'getFixed' => $fixed = (bool)rand(0, 1),
            'getExpired' => $expired = (bool)rand(0, 1),
            'getParams' => $params = uniqid(),
            'getTimestamp' => $timestamp = uniqid(),
        ]);

        $sut = $this->getSut();
        $result = $sut->createFromDto($dtoStub);

        $this->assertSame($objectId, $result['OXOBJECTID']);
        $this->assertSame($ident, $result['OXIDENT']);
        $this->assertSame($shopId, $result['OXSHOPID']);
        $this->assertSame($langId, $result['OXLANG']);
        $this->assertSame($stdUrl, $result['OXSTDURL']);
        $this->assertSame($seoUrl, $result['OXSEOURL']);
        $this->assertSame($type, $result['OXTYPE']);
        $this->assertSame((int)$fixed, $result['OXFIXED']);
        $this->assertSame((int)$expired, $result['OXEXPIRED']);
        $this->assertSame($params, $result['OXPARAMS']);
        $this->assertSame($timestamp, $result['OXTIMESTAMP']);
    }

    #[Test]
    public function createFromDtoThrowsExceptionForInvalidDto(): void
    {
        $invalidDto = $this->createStub(ExportableDtoInterface::class);

        $sut = $this->getSut();

        $this->expectException(InvalidDtoTypeException::class);
        $this->expectExceptionMessage(
            (new InvalidDtoTypeException($invalidDto, SeoUrlDtoInterface::class))->getMessage()
        );

        $sut->createFromDto($invalidDto);
    }

    private function getSut(): ArrayFactoryInterface
    {
        return new SeoUrlArrayFactory();
    }
}
