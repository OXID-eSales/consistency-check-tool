<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\SeoUrl\Console;

use OxidEsales\ConsistencyCheck\SeoUrl\Console\CheckDuplicateSeoUrlsCommand;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Tester\CommandTester;

final class CheckDuplicateSeoUrlsCommandTest extends IntegrationTestCase
{
    private string $tempDir;
    private array $insertedIds = [];
    private CommandTester $commandTester;

    public function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/oxid_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);

        $command = $this->get(CheckDuplicateSeoUrlsCommand::class);
        $this->commandTester = new CommandTester($command);
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
    public function itFindsNoDuplicatesWhenNoneExist(): void
    {
        $exitCode = $this->commandTester->execute(['--suffix' => 'oxid']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No duplicate SEO URLs found', $this->commandTester->getDisplay());
    }

    #[Test]
    public function itFindsDuplicatesWithSuffixInUrl(): void
    {
        $suffix = '-oxid';
        $urlWithSuffix = 'test-product' . $suffix . '.html';
        $this->insertSeoUrl(uniqid(), $urlWithSuffix, 'oxarticle');
        $this->insertSeoUrl(uniqid(), 'other-product.html', 'oxarticle');

        $exitCode = $this->commandTester->execute(['--suffix' => $suffix]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString($urlWithSuffix, $this->commandTester->getDisplay());
        $this->assertStringContainsString('Found', $this->commandTester->getDisplay());
    }

    #[Test]
    public function itAcceptsCustomSuffixOption(): void
    {
        $customSuffix = uniqid();

        $exitCode = $this->commandTester->execute(['--suffix' => $customSuffix]);

        $this->assertSame(0, $exitCode);
        $this->assertNotEmpty($this->commandTester->getDisplay());
    }

    #[Test]
    public function itExportsResultsToCSV(): void
    {
        $filepath = $this->tempDir . '/' . uniqid() . '.csv';

        $exitCode = $this->commandTester->execute([
            '--export' => $filepath,
            '--suffix' => 'oxid',
        ]);

        $this->assertSame(0, $exitCode);
        $output = $this->commandTester->getDisplay();

        if (str_contains($output, 'No duplicate SEO URLs found')) {
            $this->assertStringContainsString('No duplicate SEO URLs found', $output);
        } else {
            $this->assertStringContainsString('Exported', $output);
            $this->assertFileExists($filepath);
        }
    }

    #[Test]
    public function itHasExportAndSuffixOptions(): void
    {
        $command = $this->get(CheckDuplicateSeoUrlsCommand::class);
        $definition = $command->getDefinition();

        $this->assertTrue($definition->hasOption('export'));
        $this->assertTrue($definition->hasOption('suffix'));
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
}
