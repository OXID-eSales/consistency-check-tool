<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ExportByFilter\Console;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Service\ExportServiceInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Console\ExportByFilterCommand;
use OxidEsales\ConsistencyCheck\ExportByFilter\Exception\FilterNotFoundException;
use OxidEsales\ConsistencyCheck\ExportByFilter\Factory\ExportConfigurationFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Service\FilterRegistryInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\MessageFormatterServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(ExportByFilterCommand::class)]
final class ExportByFilterCommandTest extends TestCase
{
    #[Test]
    public function executeFailsWhenFilterNameMissing(): void
    {
        $messageFormatterStub = $this->createStub(MessageFormatterServiceInterface::class);
        $messageFormatterStub->method('formatError')->willReturn('The --filter-name option is required');

        $sut = $this->getSut(messageFormatter: $messageFormatterStub);
        $commandTester = new CommandTester($sut);

        $result = $commandTester->execute([]);

        $this->assertSame(Command::FAILURE, $result);
        $this->assertStringContainsString('--filter-name option is required', $commandTester->getDisplay());
    }

    #[Test]
    public function executeFailsWhenFilterNotFound(): void
    {
        $filterName = uniqid();

        $filterRegistryStub = $this->createStub(FilterRegistryInterface::class);
        $filterRegistryStub->method('get')->willThrowException(
            new FilterNotFoundException($filterName, [])
        );

        $messageFormatterStub = $this->createStub(MessageFormatterServiceInterface::class);
        $messageFormatterStub->method('formatError')->willReturn("Filter \"$filterName\" not found");

        $sut = $this->getSut(
            filterRegistry: $filterRegistryStub,
            messageFormatter: $messageFormatterStub,
        );
        $commandTester = new CommandTester($sut);

        $result = $commandTester->execute(['--filter-name' => $filterName]);

        $this->assertSame(Command::FAILURE, $result);
        $this->assertStringContainsString('not found', $commandTester->getDisplay());
    }

    #[Test]
    public function executeTriggersExportWithExpectedConfiguration(): void
    {
        $filterName = uniqid();
        $configurationStub = $this->createConfiguredStub(ExportConfigurationInterface::class, [
            'getItems' => [],
        ]);

        $configurationFactoryStub = $this->createConfiguredStub(ExportConfigurationFactoryInterface::class, [
            'createFromFilter' => $configurationStub,
        ]);

        $exportServiceSpy = $this->createMock(ExportServiceInterface::class);
        $exportServiceSpy->expects($this->once())
            ->method('export')
            ->with($configurationStub);

        $sut = $this->getSut(
            configurationFactory: $configurationFactoryStub,
            exportService: $exportServiceSpy,
        );
        $commandTester = new CommandTester($sut);

        $result = $commandTester->execute(['--filter-name' => $filterName]);

        $this->assertSame(Command::SUCCESS, $result);
    }

    private function getSut(
        ?FilterRegistryInterface $filterRegistry = null,
        ?ExportConfigurationFactoryInterface $configurationFactory = null,
        ?ExportServiceInterface $exportService = null,
        ?MessageFormatterServiceInterface $messageFormatter = null,
        ?LoggerInterface $logger = null,
    ): ExportByFilterCommand {
        $filterRegistry ??= $this->createStub(FilterRegistryInterface::class);
        $configurationFactory ??= $this->createStub(ExportConfigurationFactoryInterface::class);
        $exportService ??= $this->createStub(ExportServiceInterface::class);
        $messageFormatter ??= $this->createStub(MessageFormatterServiceInterface::class);
        $logger ??= $this->createStub(LoggerInterface::class);

        return new ExportByFilterCommand(
            filterRegistry: $filterRegistry,
            configurationFactory: $configurationFactory,
            exportService: $exportService,
            messageFormatter: $messageFormatter,
            logger: $logger,
        );
    }
}
