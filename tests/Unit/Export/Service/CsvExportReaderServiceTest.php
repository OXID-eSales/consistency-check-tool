<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Export\Service;

use League\Csv\Reader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\Export\Configuration\ExportReaderConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Exception\CsvReadException;
use OxidEsales\ConsistencyCheck\Export\Factory\CsvReaderFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;
use OxidEsales\ConsistencyCheck\Export\Service\CsvExportReaderService;
use OxidEsales\ConsistencyCheck\Export\Service\ExportReaderServiceInterface;
use OxidEsales\ConsistencyCheck\Shared\Dto\ExportableDtoInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsvExportReaderServiceTest extends TestCase
{
    private vfsStreamDirectory $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = vfsStream::setup('root');
    }

    #[Test]
    public function read(): void
    {
        $filename = uniqid() . '.csv';
        $csvContent = "col1,col2\nvalue1,value2\nvalue3,value4\n";

        vfsStream::newFile($filename)
            ->withContent($csvContent)
            ->at($this->fileSystem);

        $dto1 = $this->createStub(ExportableDtoInterface::class);
        $dto2 = $this->createStub(ExportableDtoInterface::class);

        $dtoFactoryStub = $this->createStub(DtoFactoryInterface::class);
        $callCount = 0;
        $dtoFactoryStub->method('createFromArray')
            ->willReturnCallback(function () use ($dto1, $dto2, &$callCount) {
                return $callCount++ === 0 ? $dto1 : $dto2;
            });

        $configurationStub = $this->createStub(ExportReaderConfigurationInterface::class);
        $configurationStub->method('getFilePath')->willReturn($filename);
        $configurationStub->method('getDtoFactory')->willReturn($dtoFactoryStub);

        $readerFactoryStub = $this->createStub(CsvReaderFactoryInterface::class);
        $readerFactoryStub->method('create')
            ->willReturnCallback(fn(string $path) => Reader::from($path, 'r'));

        $sut = $this->getSut(
            pathResolver: $this->createPathResolverStub(),
            readerFactory: $readerFactoryStub
        );

        $dtos = $sut->read($configurationStub);

        $this->assertCount(2, $dtos);
        $this->assertSame($dto1, $dtos[0]);
        $this->assertSame($dto2, $dtos[1]);
    }

    #[Test]
    public function readThrowsExceptionWhenFileCannotBeRead(): void
    {
        $filename = uniqid() . '.csv';

        $dtoFactoryStub = $this->createStub(DtoFactoryInterface::class);

        $configurationStub = $this->createStub(ExportReaderConfigurationInterface::class);
        $configurationStub->method('getFilePath')->willReturn($filename);
        $configurationStub->method('getDtoFactory')->willReturn($dtoFactoryStub);

        $readerFactoryStub = $this->createStub(CsvReaderFactoryInterface::class);
        $readerFactoryStub->method('create')
            ->willReturnCallback(fn(string $path) => Reader::from($path, 'r'));

        $sut = $this->getSut(
            pathResolver: $this->createPathResolverStub(),
            readerFactory: $readerFactoryStub
        );

        $this->expectException(CsvReadException::class);
        $this->expectExceptionMessage($filename);

        $sut->read($configurationStub);
    }

    private function createPathResolverStub(): PathResolverInterface
    {
        $pathResolverStub = $this->createStub(PathResolverInterface::class);
        $pathResolverStub->method('getAbsolutePath')
            ->willReturnCallback(fn(string $path) => $this->fileSystem->url() . '/' . $path);

        return $pathResolverStub;
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
