<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Export\Service;

use ArrayIterator;
use League\Csv\Reader;
use League\Csv\UnableToProcessCsv;
use OxidEsales\ConsistencyCheck\Export\Configuration\ExportReaderConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Exception\CsvReadException;
use OxidEsales\ConsistencyCheck\Export\Factory\CsvReaderFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Service\CsvExportReaderService;
use OxidEsales\ConsistencyCheck\Export\Service\ExportReaderServiceInterface;
use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsvExportReaderServiceTest extends TestCase
{
    #[Test]
    public function read(): void
    {
        $filePath = uniqid();
        $absolutePath = uniqid();
        $readerIterator = new ArrayIterator([$csvRecord = [uniqid()]]);

        $dtoStub = $this->createStub(ExportableDtoInterface::class);

        $dtoFactoryStub = $this->createMock(DtoFactoryInterface::class);
        $dtoFactoryStub->expects($this->exactly(count($readerIterator)))
            ->method('createFromArray')->with($csvRecord)->willReturn($dtoStub);

        $configurationStub = $this->createConfiguredStub(ExportReaderConfigurationInterface::class, [
            'getFilePath' => $filePath,
            'getDtoFactory' => $dtoFactoryStub,
        ]);

        $readerMock = $this->createMock(Reader::class);
        $readerMock->expects($this->once())
            ->method('setHeaderOffset')
            ->with(0);
        $readerMock->method('getIterator')
            ->willReturn($readerIterator);

        $readerFactoryMock = $this->createMock(CsvReaderFactoryInterface::class);
        $readerFactoryMock->method('create')
            ->with($absolutePath)
            ->willReturn($readerMock);

        $pathResolverMock = $this->createMock(PathResolverInterface::class);
        $pathResolverMock->method('getAbsolutePath')
            ->with($filePath)
            ->willReturn($absolutePath);

        $sut = $this->getSut(
            pathResolver: $pathResolverMock,
            readerFactory: $readerFactoryMock
        );

        $result = $sut->read($configurationStub);

        $this->assertCount(1, $result);
        $this->assertSame($dtoStub, $result[0]);
    }

    #[Test]
    public function readThrowsCsvReadExceptionWhenReaderFails(): void
    {
        $filePath = uniqid();
        $errorMessage = uniqid();
        $absolutePath = uniqid();
        $exception = new class ($errorMessage) extends \Exception implements UnableToProcessCsv {
        };

        $configurationStub = $this->createConfiguredStub(ExportReaderConfigurationInterface::class, [
            'getFilePath' => $filePath,
            'getDtoFactory' => $this->createStub(DtoFactoryInterface::class),
        ]);

        $readerFactoryStub = $this->createStub(CsvReaderFactoryInterface::class);
        $readerFactoryStub->method('create')->with($absolutePath)->willThrowException($exception);

        $pathResolverStub = $this->createStub(PathResolverInterface::class);
        $pathResolverStub->method('getAbsolutePath')->with($filePath)->willReturn($absolutePath);

        $sut = $this->getSut(
            pathResolver: $pathResolverStub,
            readerFactory: $readerFactoryStub
        );

        $this->expectException(CsvReadException::class);
        $this->expectExceptionMessage((new CsvReadException($filePath, $exception))->getMessage());

        $sut->read($configurationStub);
    }

    private function getSut(
        ?PathResolverInterface $pathResolver = null,
        ?CsvReaderFactoryInterface $readerFactory = null,
    ): ExportReaderServiceInterface {
        $pathResolver ??= $this->createStub(PathResolverInterface::class);
        $readerFactory ??= $this->createStub(CsvReaderFactoryInterface::class);

        return new CsvExportReaderService(
            pathResolver: $pathResolver,
            readerFactory: $readerFactory
        );
    }
}
