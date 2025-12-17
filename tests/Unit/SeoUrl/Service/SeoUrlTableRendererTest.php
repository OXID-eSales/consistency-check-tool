<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Service;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Service\SeoUrlTableRenderer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

final class SeoUrlTableRendererTest extends TestCase
{
    #[Test]
    public function itRendersTableWithAllHeaders(): void
    {
        $sut = new SeoUrlTableRenderer();
        $output = new BufferedOutput();

        $sut->render([], $output);

        $result = $output->fetch();
        $this->assertStringContainsString('OXOBJECTID', $result);
        $this->assertStringContainsString('OXIDENT', $result);
        $this->assertStringContainsString('OXSHOPID', $result);
        $this->assertStringContainsString('OXLANG', $result);
        $this->assertStringContainsString('OXSTDURL', $result);
        $this->assertStringContainsString('OXSEOURL', $result);
        $this->assertStringContainsString('OXTYPE', $result);
        $this->assertStringContainsString('OXFIXED', $result);
        $this->assertStringContainsString('OXEXPIRED', $result);
        $this->assertStringContainsString('OXPARAMS', $result);
        $this->assertStringContainsString('OXTIMESTAMP', $result);
    }

    #[Test]
    public function itRendersRowsFromDtos(): void
    {
        $objectId = uniqid();
        $ident = uniqid();
        $shopId = rand(1, 10);
        $languageId = rand(0, 5);
        $stdUrl = uniqid();
        $seoUrl = uniqid();
        $type = uniqid();
        $fixed = (bool)rand(0, 1);
        $expired = (bool)rand(0, 1);
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

        $sut = new SeoUrlTableRenderer();
        $output = new BufferedOutput();

        $sut->render([$dtoStub], $output);

        $result = $output->fetch();
        $this->assertStringContainsString($objectId, $result);
        $this->assertStringContainsString($ident, $result);
        $this->assertStringContainsString((string)$shopId, $result);
        $this->assertStringContainsString((string)$languageId, $result);
        $this->assertStringContainsString($seoUrl, $result);
        $this->assertStringContainsString($type, $result);
        $this->assertStringContainsString($timestamp, $result);
    }

    #[Test]
    public function itRendersMultipleRows(): void
    {
        $objectId1 = uniqid();
        $seoUrl1 = uniqid();
        $objectId2 = uniqid();
        $seoUrl2 = uniqid();

        $dtoStub1 = $this->createConfiguredStub(SeoUrlDtoInterface::class, [
            'getObjectId' => $objectId1,
            'getIdent' => uniqid(),
            'getShopId' => rand(1, 10),
            'getLanguageId' => rand(0, 5),
            'getStdUrl' => uniqid(),
            'getSeoUrl' => $seoUrl1,
            'getType' => uniqid(),
            'getFixed' => (bool)rand(0, 1),
            'getExpired' => (bool)rand(0, 1),
            'getParams' => uniqid(),
            'getTimestamp' => date('Y-m-d H:i:s'),
        ]);

        $dtoStub2 = $this->createConfiguredStub(SeoUrlDtoInterface::class, [
            'getObjectId' => $objectId2,
            'getIdent' => uniqid(),
            'getShopId' => rand(1, 10),
            'getLanguageId' => rand(0, 5),
            'getStdUrl' => uniqid(),
            'getSeoUrl' => $seoUrl2,
            'getType' => uniqid(),
            'getFixed' => (bool)rand(0, 1),
            'getExpired' => (bool)rand(0, 1),
            'getParams' => uniqid(),
            'getTimestamp' => date('Y-m-d H:i:s'),
        ]);

        $sut = new SeoUrlTableRenderer();
        $output = new BufferedOutput();

        $sut->render([$dtoStub1, $dtoStub2], $output);

        $result = $output->fetch();
        $this->assertStringContainsString($objectId1, $result);
        $this->assertStringContainsString($objectId2, $result);
        $this->assertStringContainsString($seoUrl1, $result);
        $this->assertStringContainsString($seoUrl2, $result);
    }
}
