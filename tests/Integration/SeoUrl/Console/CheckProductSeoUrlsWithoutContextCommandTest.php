<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\SeoUrl\Console;

use OxidEsales\ConsistencyCheck\SeoUrl\Console\CheckProductSeoUrlsWithoutContextCommand;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Tester\CommandTester;

final class CheckProductSeoUrlsWithoutContextCommandTest extends IntegrationTestCase
{
    private CommandTester $commandTester;

    public function setUp(): void
    {
        parent::setUp();

        $this->get(QueryBuilderFactoryInterface::class)->create()->delete('oxseo')->execute();
        $this->commandTester = new CommandTester($this->get(CheckProductSeoUrlsWithoutContextCommand::class));
    }

    #[Test]
    public function itListsProductUrlsWithoutContext(): void
    {
        $productId = uniqid();
        $productWithContextId = uniqid();
        $vendorId = uniqid();
        $this->insertProduct($productId, $vendorId);
        $this->insertProduct($productWithContextId, $vendorId);
        $seoUrlWithoutContext = 'without-context-' . uniqid() . '.html';
        $seoUrlWithContext = 'with-context-' . uniqid() . '.html';
        $this->insertProductSeoUrl(
            $productId,
            $seoUrlWithoutContext,
            'index.php?cl=details&amp;anid=' . $productId,
            $vendorId
        );
        $this->insertProductSeoUrl(
            $productWithContextId,
            $seoUrlWithContext,
            'index.php?cl=details&amp;anid=' . $productWithContextId . '&amp;cnid=v_' . $vendorId
            . '&amp;listtype=vendor',
            $vendorId
        );

        $exitCode = $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString($seoUrlWithoutContext, $output);
        $this->assertStringNotContainsString($seoUrlWithContext, $output);
        $this->assertStringContainsString('Found 1 fixed product SEO URLs without context', $output);
    }

    #[Test]
    public function itReportsWhenNothingIsFound(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString(
            'No fixed product SEO URLs without context found',
            $this->commandTester->getDisplay()
        );
    }

    #[Test]
    public function itHasExportOption(): void
    {
        $command = $this->get(CheckProductSeoUrlsWithoutContextCommand::class);

        $this->assertTrue($command->getDefinition()->hasOption('export'));
        $this->assertSame('oe:consistency_check:check-product-seo-urls-without-context', $command->getName());
    }

    private function insertProduct(string $productId, string $vendorId): void
    {
        $product = oxNew(Article::class);
        $product->setId($productId);
        $product->setSkipAssign(true);
        $product->assign([
            'oxartnum' => uniqid(),
            'oxtitle' => uniqid(),
            'oxactive' => 1,
            'oxvendorid' => $vendorId,
        ]);
        $product->save();
    }

    private function insertProductSeoUrl(string $productId, string $seoUrl, string $stdUrl, string $params): void
    {
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder
            ->insert('oxseo')
            ->values([
                'OXOBJECTID' => ':objectId',
                'OXIDENT' => ':ident',
                'OXSHOPID' => ':shopId',
                'OXLANG' => ':lang',
                'OXSTDURL' => ':stdUrl',
                'OXSEOURL' => ':seoUrl',
                'OXTYPE' => ':type',
                'OXFIXED' => ':fixed',
                'OXEXPIRED' => ':expired',
                'OXPARAMS' => ':params',
            ])
            ->setParameters([
                'objectId' => $productId,
                'ident' => md5($seoUrl),
                'shopId' => 1,
                'lang' => 0,
                'stdUrl' => $stdUrl,
                'seoUrl' => $seoUrl,
                'type' => 'oxarticle',
                'fixed' => 1,
                'expired' => 0,
                'params' => $params,
            ])
            ->execute();
    }
}
