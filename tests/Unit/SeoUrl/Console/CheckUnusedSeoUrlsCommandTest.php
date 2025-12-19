<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Console;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\ExportConfigurationFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Service\ExportServiceInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Console\CheckUnusedSeoUrlsCommand;
use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\Export\Exception\ExportDirectoryNotFoundException;
use OxidEsales\ConsistencyCheck\SeoUrl\Service\SeoUrlServiceInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Service\SeoUrlTableRendererInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\MessageFormatterServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class CheckUnusedSeoUrlsCommandTest extends TestCase
{
    #[Test]
    public function itThrowsExceptionWhenExportDirectoryDoesNotExist(): void
    {
        $dtoStub = $this->createConfiguredStub(SeoUrlDtoInterface::class, [
            'getObjectId' => uniqid(),
            'getIdent' => uniqid(),
            'getShopId' => rand(1, 10),
            'getLanguageId' => rand(0, 5),
            'getStdUrl' => uniqid(),
            'getSeoUrl' => uniqid(),
            'getType' => uniqid(),
            'getFixed' => (bool)rand(0, 1),
            'getExpired' => (bool)rand(0, 1),
            'getParams' => uniqid(),
            'getTimestamp' => date('Y-m-d H:i:s'),
        ]);

        $serviceStub = $this->createConfiguredStub(SeoUrlServiceInterface::class, [
            'findUnusedUrls' => [$dtoStub],
        ]);

        $configurationStub = $this->createConfiguredStub(ExportConfigurationInterface::class, [
            'getFilePrefix' => uniqid(),
        ]);

        $configurationFactoryStub = $this->createStub(ExportConfigurationFactoryInterface::class);
        $configurationFactoryStub->method('create')->willReturn($configurationStub);

        $exportServiceStub = $this->createStub(ExportServiceInterface::class);
        $exportServiceStub->method('export')->willThrowException(
            new ExportDirectoryNotFoundException('/non/existent/directory')
        );

        $sut = new CheckUnusedSeoUrlsCommand(
            $serviceStub,
            $exportServiceStub,
            $configurationFactoryStub,
            $this->createStub(SeoUrlTableRendererInterface::class),
            $this->createStub(MessageFormatterServiceInterface::class),
            $this->createStub(LoggerInterface::class),
        );

        $commandTester = new CommandTester($sut);

        $this->expectException(ExportDirectoryNotFoundException::class);

        $commandTester->execute(['--export' => true]);
    }
}
