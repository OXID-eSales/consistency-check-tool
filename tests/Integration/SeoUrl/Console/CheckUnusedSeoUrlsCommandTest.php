<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\SeoUrl\Console;

use OxidEsales\ConsistencyCheck\SeoUrl\Console\CheckUnusedSeoUrlsCommand;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class CheckUnusedSeoUrlsCommandTest extends IntegrationTestCase
{
    private string $tempDir;
    private array $insertedIds = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/oxid_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);

        if (!is_dir('export')) {
            mkdir('export', 0777, true);
        }
    }

    public function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            array_map('unlink', glob($this->tempDir . '/*'));
            rmdir($this->tempDir);
        }

        if (!empty($this->insertedIds)) {
            $qb = $this->get(QueryBuilderFactoryInterface::class)->create();
            $qb->delete('oxseo')
                ->where($qb->expr()->in('OXOBJECTID', ':ids'))
                ->setParameter('ids', $this->insertedIds, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                ->execute();
        }

        parent::tearDown();
    }

    #[Test]
    public function itSuccessfullyFindsUnusedSeoUrls(): void
    {
        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:check-unused-seo-urls'));
        $exitCode = $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertSame(0, $exitCode);
        $this->assertNotEmpty($output);
    }

    #[Test]
    public function itExportsToCSV(): void
    {
        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:check-unused-seo-urls'));
        $exitCode = $commandTester->execute(['--export' => true]);

        $output = $commandTester->getDisplay();
        $this->assertSame(0, $exitCode);

        if (str_contains($output, 'No unused SEO URLs found')) {
            $this->assertStringContainsString('No unused SEO URLs found', $output);
        } else {
            $this->assertStringContainsString('Exported', $output);
        }
    }

    #[Test]
    public function itDisplaysResultsMessageWhenUnusedUrlsFound(): void
    {
        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:check-unused-seo-urls'));
        $exitCode = $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertSame(0, $exitCode);

        $this->assertTrue(
            str_contains($output, 'No unused SEO URLs found') ||
            str_contains($output, 'Found') && str_contains($output, 'unused SEO URLs')
        );
    }

    #[Test]
    public function itConfiguresExportOption(): void
    {
        $sut = $this->getSut();

        $definition = $sut->getDefinition();

        $this->assertTrue($definition->hasOption('export'));
        $this->assertSame('Export results to CSV file', $definition->getOption('export')->getDescription());
        $this->assertSame('Check for unused SEO URLs', $sut->getDescription());
    }

    #[Test]
    public function itDisplaysTableWhenUnusedUrlsAreFound(): void
    {
        $orphanedObjectId = uniqid();
        $orphanedSeoUrl = 'orphaned-product-' . uniqid() . '.html';

        $this->insertSeoUrl($orphanedObjectId, $orphanedSeoUrl, 'oxarticle');

        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:check-unused-seo-urls'));
        $exitCode = $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString($orphanedSeoUrl, $output);
        $this->assertStringContainsString('Found', $output);
    }

    #[Test]
    public function itDisplaysNoResultsMessageWhenNoUnusedUrlsFound(): void
    {
        $this->cleanupAllOrphanedSeoUrls();

        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:check-unused-seo-urls'));
        $exitCode = $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No unused SEO URLs found', $output);
    }

    #[Test]
    public function itSkipsEntitiesWithNullReferenceTable(): void
    {
        $orphanedStaticUrlObjectId = uniqid();
        $orphanedStaticUrl = 'static-orphaned-' . uniqid() . '.html';
        $this->insertSeoUrl($orphanedStaticUrlObjectId, $orphanedStaticUrl, 'static');

        $orphanedDynamicUrlObjectId = uniqid();
        $orphanedDynamicUrl = 'dynamic-orphaned-' . uniqid() . '.html';
        $this->insertSeoUrl($orphanedDynamicUrlObjectId, $orphanedDynamicUrl, 'dynamic');

        $orphanedArticleObjectId = uniqid();
        $orphanedArticleUrl = 'article-orphaned-' . uniqid() . '.html';
        $this->insertSeoUrl($orphanedArticleObjectId, $orphanedArticleUrl, 'oxarticle');

        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:check-unused-seo-urls'));
        $exitCode = $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertSame(0, $exitCode);
        $this->assertStringNotContainsString($orphanedStaticUrl, $output);
        $this->assertStringNotContainsString($orphanedDynamicUrl, $output);
        $this->assertStringContainsString($orphanedArticleUrl, $output);
    }

    private function cleanupAllOrphanedSeoUrls(): void
    {
        $qb = $this->get(QueryBuilderFactoryInterface::class)->create();
        $qb->delete('oxseo')
            ->where('OXOBJECTID NOT IN (SELECT OXID FROM oxarticles)')
            ->andWhere('OXOBJECTID NOT IN (SELECT OXID FROM oxcategories)')
            ->andWhere('OXOBJECTID NOT IN (SELECT OXID FROM oxmanufacturers)')
            ->andWhere('OXOBJECTID NOT IN (SELECT OXID FROM oxcontents)')
            ->execute();
    }

    private function insertSeoUrl(string $objectId, string $seoUrl, string $type): void
    {
        $this->insertedIds[] = $objectId;

        $qb = $this->get(QueryBuilderFactoryInterface::class)->create();
        $qb->insert('oxseo')
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
                'objectId' => $objectId,
                'ident' => md5($seoUrl),
                'shopId' => 1,
                'lang' => 0,
                'stdUrl' => 'index.php?cl=details&anid=' . $objectId,
                'seoUrl' => $seoUrl,
                'type' => $type,
                'fixed' => 0,
                'expired' => 0,
                'params' => '',
            ])
            ->execute();
    }

    private function getSut(): CheckUnusedSeoUrlsCommand
    {
        return $this->get(CheckUnusedSeoUrlsCommand::class);
    }
}
