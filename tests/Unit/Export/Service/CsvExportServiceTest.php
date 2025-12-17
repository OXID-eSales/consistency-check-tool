<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Export\Service;

use League\Csv\UnableToProcessCsv;
use League\Csv\Writer;
use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Exception\CsvExportException;
use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\CsvWriterFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Service\CsvExportService;
use OxidEsales\ConsistencyCheck\Export\Service\ExportServiceInterface;
use OxidEsales\ConsistencyCheck\Shared\Dto\ExportableDtoInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsvExportServiceTest extends TestCase
{
    #[Test]
    public function export(): void
    {
        $filePath = uniqid();
        $absolutePath = uniqid();
        $dtoStub = $this->createStub(ExportableDtoInterface::class);

        $arrayFactoryStub = $this->createStub(ArrayFactoryInterface::class);
        $arrayFactoryStub->method('createFromDto')->with($dtoStub)->willReturn($dtoArray = [uniqid()]);

        $configurationStub = $this->createConfiguredStub(ExportConfigurationInterface::class, [
            'getHeaders' => $headers = [uniqid()],
            'getItems' => [$dtoStub],
            'getArrayFactory' => $arrayFactoryStub,
            'getFilePath' => $filePath,
        ]);

        $writerSpy = $this->createMock(Writer::class);
        $writerSpy->expects($this->exactly(2))
            ->method('insertOne')
            ->with($this->logicalOr($headers, $dtoArray));

        $writerFactoryMock = $this->createMock(CsvWriterFactoryInterface::class);
        $writerFactoryMock->method('create')
            ->with($absolutePath)
            ->willReturn($writerSpy);

        $pathResolverMock = $this->createMock(PathResolverInterface::class);
        $pathResolverMock->method('getAbsolutePath')
            ->with($filePath)
            ->willReturn($absolutePath);

        $sut = $this->getSut(
            pathResolver: $pathResolverMock,
            writerFactory: $writerFactoryMock
        );

        $sut->export($configurationStub);
    }

    #[Test]
    public function exportThrowsCsvExportExceptionWhenWriterFails(): void
    {
        $filePath = uniqid();
        $absolutePath = uniqid();
        $errorMessage = uniqid();
        $exception = new class ($errorMessage) extends \Exception implements UnableToProcessCsv {
        };

        $configurationStub = $this->createConfiguredStub(ExportConfigurationInterface::class, [
            'getHeaders' => [uniqid()],
            'getItems' => [],
            'getFilePath' => $filePath,
        ]);

        $writerFactoryStub = $this->createStub(CsvWriterFactoryInterface::class);
        $writerFactoryStub->method('create')->with($absolutePath)
            ->willThrowException($exception);

        $pathResolverStub = $this->createStub(PathResolverInterface::class);
        $pathResolverStub->method('getAbsolutePath')->with($filePath)->willReturn($absolutePath);

        $sut = $this->getSut(
            pathResolver: $pathResolverStub,
            writerFactory: $writerFactoryStub
        );

        $this->expectException(CsvExportException::class);
        $this->expectExceptionMessage(
            (new CsvExportException($filePath, $exception))->getMessage()
        );

        $sut->export($configurationStub);
    }

    private function getSut(
        ?PathResolverInterface $pathResolver = null,
        ?CsvWriterFactoryInterface $writerFactory = null,
    ): ExportServiceInterface {
        $pathResolver ??= $this->createStub(PathResolverInterface::class);
        $writerFactory ??= $this->createStub(CsvWriterFactoryInterface::class);

        return new CsvExportService(
            pathResolver: $pathResolver,
            writerFactory: $writerFactory
        );
    }
}
