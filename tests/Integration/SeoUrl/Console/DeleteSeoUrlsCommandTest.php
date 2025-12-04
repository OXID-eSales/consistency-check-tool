<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\SeoUrl\Console;

use OxidEsales\ConsistencyCheck\SeoUrl\Console\DeleteSeoUrlsCommand;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class DeleteSeoUrlsCommandTest extends IntegrationTestCase
{
    private string $tempDir;

    public function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/oxid_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    public function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            array_map('unlink', glob($this->tempDir . '/*'));
            rmdir($this->tempDir);
        }
        parent::tearDown();
    }

    #[Test]
    public function itSuccessfullyDeletesSeoUrlsFromCsv(): void
    {
        $id1 = uniqid();
        $id2 = uniqid();
        $csvContent = <<<CSV
OXOBJECTID,OXIDENT,OXSHOPID,OXLANG,OXSTDURL,OXSEOURL,OXTYPE,OXFIXED,OXEXPIRED,OXPARAMS,OXTIMESTAMP
{$id1},ident-{$id1},1,0,std-url-{$id1},seo-url-{$id1},oxarticle,0,0,params1,2024-01-01 00:00:00
{$id2},ident-{$id2},1,0,std-url-{$id2},seo-url-{$id2},oxcategory,0,0,params2,2024-01-02 00:00:00
CSV;
        $filename = uniqid() . '.csv';
        $filepath = $this->tempDir . '/' . $filename;
        file_put_contents($filepath, $csvContent);

        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:delete-seo-urls'));
        $exitCode = $commandTester->execute(['--file' => $filepath]);

        $output = $commandTester->getDisplay();
        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Reading CSV file', $output);
        $this->assertStringContainsString('Processing 2 SEO URLs', $output);
        $this->assertStringContainsString('Deleted', $output);
    }

    #[Test]
    public function itPerformsDryRunWithoutActualDeletion(): void
    {
        $id1 = uniqid();
        $id2 = uniqid();
        $csvContent = <<<CSV
OXOBJECTID,OXIDENT,OXSHOPID,OXLANG,OXSTDURL,OXSEOURL,OXTYPE,OXFIXED,OXEXPIRED,OXPARAMS,OXTIMESTAMP
{$id1},ident-{$id1},1,0,std-url-{$id1},seo-url-{$id1},oxarticle,0,0,params1,2024-01-01 00:00:00
{$id2},ident-{$id2},1,0,std-url-{$id2},seo-url-{$id2},oxcategory,0,0,params2,2024-01-02 00:00:00
CSV;
        $filename = uniqid() . '.csv';
        $filepath = $this->tempDir . '/' . $filename;
        file_put_contents($filepath, $csvContent);

        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:delete-seo-urls'));
        $exitCode = $commandTester->execute(['--file' => $filepath, '--dry-run' => true]);

        $output = $commandTester->getDisplay();
        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Reading CSV file', $output);
        $this->assertStringContainsString('Dry run: Would delete 2 SEO URLs', $output);
        $this->assertStringNotContainsString('Processing', $output);
        $this->assertStringNotContainsString('Deleted', $output);
    }

    #[Test]
    public function itDisplaysTableInDryRunMode(): void
    {
        $objectId = uniqid();
        $seoUrl = 'seo-url-' . uniqid();
        $csvContent = <<<CSV
OXOBJECTID,OXIDENT,OXSHOPID,OXLANG,OXSTDURL,OXSEOURL,OXTYPE,OXFIXED,OXEXPIRED,OXPARAMS,OXTIMESTAMP
{$objectId},ident-{$objectId},1,0,standard-url-{$objectId},{$seoUrl},oxarticle,0,0,params1,2024-01-01 00:00:00
CSV;
        $filename = uniqid() . '.csv';
        $filepath = $this->tempDir . '/' . $filename;
        file_put_contents($filepath, $csvContent);

        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:delete-seo-urls'));
        $exitCode = $commandTester->execute(['--file' => $filepath, '--dry-run' => true]);

        $output = $commandTester->getDisplay();
        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString($objectId, $output);
        $this->assertStringContainsString($seoUrl, $output);
        $this->assertStringContainsString('OXOBJECTID', $output);
    }

    #[Test]
    public function itHasDryRunOption(): void
    {
        $sut = $this->getSut();
        $definition = $sut->getDefinition();

        $this->assertTrue($definition->hasOption('dry-run'));
        $dryRunOption = $definition->getOption('dry-run');
        $this->assertSame('Perform a dry run without actual deletions', $dryRunOption->getDescription());
    }

    #[Test]
    public function itFailsWhenNoFileOptionProvided(): void
    {
        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:delete-seo-urls'));
        $exitCode = $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('--file option is required', $output);
        $this->assertSame(1, $exitCode);
    }

    #[Test]
    public function itSucceedsWhenCsvIsEmpty(): void
    {
        $csvContent = <<<CSV
OXOBJECTID,OXIDENT,OXSHOPID,OXLANG,OXSTDURL,OXSEOURL,OXTYPE,OXFIXED,OXEXPIRED,OXPARAMS,OXTIMESTAMP
CSV;
        $filename = uniqid() . '.csv';
        $filepath = $this->tempDir . '/' . $filename;
        file_put_contents($filepath, $csvContent);

        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:delete-seo-urls'));
        $exitCode = $commandTester->execute(['--file' => $filepath]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('No SEO URLs found in CSV file', $output);
        $this->assertSame(0, $exitCode);
    }

    private function getSut(): DeleteSeoUrlsCommand
    {
        return $this->get(DeleteSeoUrlsCommand::class);
    }
}
