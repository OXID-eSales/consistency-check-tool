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
        $stdUrl = 'index.php?cl=test&id=' . uniqid();
        $seoUrl = 'test-product-' . uniqid() . '.html';
        $type = 'oxarticle';
        $fixed = rand(0, 1);
        $expired = rand(0, 1);
        $params = 'param=' . uniqid();
        $timestamp = '2024-01-01 12:00:00';

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
        $dtoStub1 = $this->createConfiguredStub(SeoUrlDtoInterface::class, [
            'getObjectId' => 'object-1',
            'getIdent' => 'ident-1',
            'getShopId' => 1,
            'getLanguageId' => 0,
            'getStdUrl' => 'std-url-1',
            'getSeoUrl' => 'seo-url-1.html',
            'getType' => 'oxarticle',
            'getFixed' => 0,
            'getExpired' => 0,
            'getParams' => '',
            'getTimestamp' => '2024-01-01',
        ]);

        $dtoStub2 = $this->createConfiguredStub(SeoUrlDtoInterface::class, [
            'getObjectId' => 'object-2',
            'getIdent' => 'ident-2',
            'getShopId' => 1,
            'getLanguageId' => 1,
            'getStdUrl' => 'std-url-2',
            'getSeoUrl' => 'seo-url-2.html',
            'getType' => 'oxcategory',
            'getFixed' => 1,
            'getExpired' => 0,
            'getParams' => '',
            'getTimestamp' => '2024-01-02',
        ]);

        $sut = new SeoUrlTableRenderer();
        $output = new BufferedOutput();

        $sut->render([$dtoStub1, $dtoStub2], $output);

        $result = $output->fetch();
        $this->assertStringContainsString('object-1', $result);
        $this->assertStringContainsString('object-2', $result);
        $this->assertStringContainsString('seo-url-1.html', $result);
        $this->assertStringContainsString('seo-url-2.html', $result);
    }
}
