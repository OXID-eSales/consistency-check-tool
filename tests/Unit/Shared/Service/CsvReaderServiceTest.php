<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Shared\Service;

use League\Csv\Reader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\Shared\Exception\CsvReadException;
use OxidEsales\ConsistencyCheck\Shared\Factory\CsvReaderFactoryInterface;
use OxidEsales\ConsistencyCheck\Shared\Mapper\CsvMapperInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\CsvReaderService;
use OxidEsales\ConsistencyCheck\Shared\Service\CsvReaderServiceInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsvReaderServiceTest extends TestCase
{
    private vfsStreamDirectory $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = vfsStream::setup('root');
    }

    #[Test]
    public function readFromCsvReturnsArrayOfDtos(): void
    {
        $filename = uniqid() . '.csv';
        $csvContent = "col1,col2\nvalue1,value2\nvalue3,value4\n";

        vfsStream::newFile($filename)
            ->withContent($csvContent)
            ->at($this->fileSystem);

        $dto1 = new \stdClass();
        $dto2 = new \stdClass();

        $mapperStub = $this->createStub(CsvMapperInterface::class);
        $callCount = 0;
        $mapperStub->method('fromArray')
            ->willReturnCallback(function () use ($dto1, $dto2, &$callCount) {
                return $callCount++ === 0 ? $dto1 : $dto2;
            });

        $readerFactoryStub = $this->createStub(CsvReaderFactoryInterface::class);
        $readerFactoryStub->method('create')
            ->willReturnCallback(fn(string $path) => Reader::from($path, 'r'));

        $sut = $this->getSut(
            pathResolver: $this->createPathResolverStub(),
            readerFactory: $readerFactoryStub
        );
        $dtos = $sut->readFromCsv($filename, $mapperStub);

        $this->assertCount(2, $dtos);
        $this->assertSame($dto1, $dtos[0]);
        $this->assertSame($dto2, $dtos[1]);
    }

    #[Test]
    public function readFromCsvThrowsExceptionWhenFileCannotBeRead(): void
    {
        $filename = uniqid() . '.csv';

        $mapperStub = $this->createStub(CsvMapperInterface::class);

        $readerFactoryStub = $this->createStub(CsvReaderFactoryInterface::class);
        $readerFactoryStub->method('create')
            ->willReturnCallback(fn(string $path) => Reader::from($path, 'r'));

        $sut = $this->getSut(
            pathResolver: $this->createPathResolverStub(),
            readerFactory: $readerFactoryStub
        );

        $this->expectException(CsvReadException::class);
        $this->expectExceptionMessage($filename);

        $sut->readFromCsv($filename, $mapperStub);
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
    ): CsvReaderServiceInterface {
        $pathResolver ??= $this->createStub(PathResolverInterface::class);
        $readerFactory ??= $this->createStub(CsvReaderFactoryInterface::class);

        return new CsvReaderService(
            pathResolver: $pathResolver,
            readerFactory: $readerFactory
        );
    }
}
