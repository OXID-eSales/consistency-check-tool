<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Mapper;

use OxidEsales\ConsistencyCheck\Shared\Mapper\CsvMapperInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\DTO\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Mapper\SeoUrlCsvMapper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoUrlCsvMapperTest extends TestCase
{
    #[Test]
    public function getHeaders(): void
    {
        $sut = $this->getSut();
        $headers = $sut->getHeaders();

        $expectedHeaders = [
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

        $this->assertSame($expectedHeaders, $headers);
    }

    #[Test]
    public function toArray(): void
    {
        $objectId = uniqid();
        $ident = uniqid();
        $shopId = rand(1, 10);
        $languageId = rand(0, 5);
        $stdUrl = uniqid();
        $seoUrl = uniqid() . '.html';
        $type = uniqid();
        $fixed = rand(0, 1);
        $expired = rand(0, 1);
        $params = uniqid();
        $timestamp = date('Y-m-d H:i:s');

        $dtoStub = $this->createConfiguredStub(SeoUrlDtoInterface::class, [
            'getObjectId' => $objectId,
            'getIdent' => $ident,
            'getShopId' => $shopId,
            'getLanguageId' => $languageId,
            'getStdUrl' => $stdUrl,
            'getSeoUrl' => $seoUrl,
            'getType' => $type,
            'getFixed' => $fixed,
            'getExpired' => $expired,
            'getParams' => $params,
            'getTimestamp' => $timestamp,
        ]);

        $sut = $this->getSut();
        $result = $sut->toArray($dtoStub);

        $this->assertSame([
            'OXOBJECTID' => $objectId,
            'OXIDENT' => $ident,
            'OXSHOPID' => (string)$shopId,
            'OXLANG' => (string)$languageId,
            'OXSTDURL' => $stdUrl,
            'OXSEOURL' => $seoUrl,
            'OXTYPE' => $type,
            'OXFIXED' => (string)$fixed,
            'OXEXPIRED' => (string)$expired,
            'OXPARAMS' => $params,
            'OXTIMESTAMP' => $timestamp,
        ], $result);
    }

    #[Test]
    public function fromArray(): void
    {
        $objectId = uniqid();
        $ident = uniqid();
        $shopId = rand(1, 10);
        $languageId = rand(0, 5);
        $stdUrl = uniqid();
        $seoUrl = uniqid() . '.html';
        $type = uniqid();
        $fixed = rand(0, 1);
        $expired = rand(0, 1);
        $params = uniqid();
        $timestamp = date('Y-m-d H:i:s');

        $data = [
            'OXOBJECTID' => $objectId,
            'OXIDENT' => $ident,
            'OXSHOPID' => (string)$shopId,
            'OXLANG' => (string)$languageId,
            'OXSTDURL' => $stdUrl,
            'OXSEOURL' => $seoUrl,
            'OXTYPE' => $type,
            'OXFIXED' => (string)$fixed,
            'OXEXPIRED' => (string)$expired,
            'OXPARAMS' => $params,
            'OXTIMESTAMP' => $timestamp,
        ];

        $sut = $this->getSut();
        $result = $sut->fromArray($data);

        $this->assertInstanceOf(SeoUrlDtoInterface::class, $result);
        $this->assertSame($objectId, $result->getObjectId());
        $this->assertSame($ident, $result->getIdent());
        $this->assertSame($shopId, $result->getShopId());
        $this->assertSame($languageId, $result->getLanguageId());
        $this->assertSame($stdUrl, $result->getStdUrl());
        $this->assertSame($seoUrl, $result->getSeoUrl());
        $this->assertSame($type, $result->getType());
        $this->assertSame($fixed, $result->getFixed());
        $this->assertSame($expired, $result->getExpired());
        $this->assertSame($params, $result->getParams());
        $this->assertSame($timestamp, $result->getTimestamp());
    }

    #[Test]
    public function fromArrayHandlesMissingFields(): void
    {
        $data = [];

        $sut = $this->getSut();
        $result = $sut->fromArray($data);

        $this->assertInstanceOf(SeoUrlDtoInterface::class, $result);
        $this->assertSame('', $result->getObjectId());
        $this->assertSame('', $result->getIdent());
        $this->assertSame(0, $result->getShopId());
        $this->assertSame(0, $result->getLanguageId());
        $this->assertSame('', $result->getStdUrl());
        $this->assertSame('', $result->getSeoUrl());
        $this->assertSame('', $result->getType());
        $this->assertSame(0, $result->getFixed());
        $this->assertSame(0, $result->getExpired());
        $this->assertSame('', $result->getParams());
        $this->assertSame('', $result->getTimestamp());
    }

    #[Test]
    public function fromArrayConvertsStringToInt(): void
    {
        $data = [
            'OXOBJECTID' => uniqid(),
            'OXIDENT' => uniqid(),
            'OXSHOPID' => '5',
            'OXLANG' => '2',
            'OXSTDURL' => uniqid(),
            'OXSEOURL' => uniqid() . '.html',
            'OXTYPE' => uniqid(),
            'OXFIXED' => '1',
            'OXEXPIRED' => '0',
            'OXPARAMS' => uniqid(),
            'OXTIMESTAMP' => date('Y-m-d H:i:s'),
        ];

        $sut = $this->getSut();
        $result = $sut->fromArray($data);

        $this->assertSame(5, $result->getShopId());
        $this->assertSame(2, $result->getLanguageId());
        $this->assertSame(1, $result->getFixed());
        $this->assertSame(0, $result->getExpired());
    }

    private function getSut(): CsvMapperInterface
    {
        return new SeoUrlCsvMapper();
    }
}
