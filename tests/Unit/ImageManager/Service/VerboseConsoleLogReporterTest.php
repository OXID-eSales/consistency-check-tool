<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Service;

use OxidEsales\ConsistencyCheck\ImageManager\Service\LogReaderInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\MessageFormatterServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\VerboseConsoleLogReporter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class VerboseConsoleLogReporterTest extends TestCase
{
    #[Test]
    public function itSkipsLogOutputWhenVerbosityIsTooLow(): void
    {
        $logReaderStub = $this->createStub(LogReaderInterface::class);
        $logReaderStub->expects($this->never())->method('readLines');

        $formatterStub = $this->createStub(MessageFormatterServiceInterface::class);
        $formatterStub->expects($this->never())->method('formatInfo');

        $output = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL);

        $sut = new VerboseConsoleLogReporter($logReaderStub, $formatterStub);
        $sut->after($output);

        $this->assertSame('', $output->fetch());
    }

    #[Test]
    public function itOutputsFormattedEmptyMessageWhenLogIsEmpty(): void
    {
        $logReaderStub = $this->createStub(LogReaderInterface::class);
        $logReaderStub->method('readLines')->willReturn([]);

        $formatterMock = $this->createMock(MessageFormatterServiceInterface::class);
        $formatterMock->method('formatInfo')->willReturnCallback(fn($msg) => "[INFO] $msg");
        $formatterMock->method('formatComment')->willReturnCallback(fn($msg) => "[COMMENT] $msg");

        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);

        $sut = new VerboseConsoleLogReporter($logReaderStub, $formatterMock);
        $sut->after($output);

        $text = $output->fetch();

        $this->assertStringContainsString('[INFO] --- Log Output ---', $text);
        $this->assertStringContainsString('[COMMENT] (Log file is empty or missing)', $text);
    }

    #[Test]
    public function itOutputsLogLinesAndEndMarkers(): void
    {
        $logReaderStub = $this->createStub(LogReaderInterface::class);
        $logReaderStub->method('readLines')->willReturn([
            $expectedLine1 = uniqid(),
            $expectedLine2 = uniqid(),
        ]);

        $formatterMock = $this->createMock(MessageFormatterServiceInterface::class);
        $formatterMock->method('formatInfo')->willReturnCallback(fn($msg) => "[INFO] $msg");

        $output = new BufferedOutput(OutputInterface::VERBOSITY_VERBOSE);

        $sut = new VerboseConsoleLogReporter($logReaderStub, $formatterMock);
        $sut->after($output);

        $text = $output->fetch();

        $this->assertStringContainsString('[INFO] --- Log Output ---', $text);
        $this->assertStringContainsString($expectedLine1, $text);
        $this->assertStringContainsString($expectedLine2, $text);
        $this->assertStringContainsString('[INFO] --- End of Log ---', $text);
    }
}
