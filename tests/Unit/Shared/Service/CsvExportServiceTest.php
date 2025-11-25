<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Shared\Service;

use League\Csv\Writer;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\Shared\Exception\InvalidFileFormatException;
use OxidEsales\ConsistencyCheck\Shared\Factory\CsvWriterFactoryInterface;
use OxidEsales\ConsistencyCheck\Shared\Mapper\CsvMapperInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\CsvExportService;
use OxidEsales\ConsistencyCheck\Shared\Service\CsvExportServiceInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsvExportServiceTest extends TestCase
{
    private vfsStreamDirectory $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = vfsStream::setup('root');
    }

    #[Test]
    public function exportToCsv(): void
    {
        $value1 = uniqid();
        $value2 = uniqid();
        $value3 = uniqid();
        $value4 = uniqid();

        $dto1 = new \stdClass();
        $dto2 = new \stdClass();

        $data = [$dto1, $dto2];
        $filepath = uniqid() . '.csv';

        $mapperStub = $this->createStub(CsvMapperInterface::class);
        $mapperStub->method('getHeaders')->willReturn(['field1', 'field2']);
        $mapperStub->method('toArray')->willReturnOnConsecutiveCalls(
            ['field1' => $value1, 'field2' => $value2],
            ['field1' => $value3, 'field2' => $value4]
        );

        $sut = $this->getSut(
            pathResolver: $this->createPathResolverStub(),
            writerFactory: $this->createWriterFactoryStub()
        );
        $count = $sut->exportToCsv($data, $filepath, $mapperStub);

        $this->assertSame(2, $count);
        $content = file_get_contents($this->fileSystem->url() . '/' . $filepath);
        $this->assertStringContainsString('field1', $content);
        $this->assertStringContainsString($value1, $content);
        $this->assertStringContainsString($value2, $content);
        $this->assertStringContainsString($value3, $content);
        $this->assertStringContainsString($value4, $content);
    }

    #[Test]
    public function exportToCsvThrowsExceptionWhenFileCannotBeOpened(): void
    {
        $filename = uniqid() . '.csv';

        $dto = new \stdClass();
        $data = [$dto];

        $mapperStub = $this->createStub(CsvMapperInterface::class);
        $mapperStub->method('getHeaders')->willReturn(['col1']);
        $mapperStub->method('toArray')->willReturn(['col1' => 'value1']);

        // Create a directory where file should be (blocks file creation)
        vfsStream::newDirectory($filename)->at($this->fileSystem);

        $sut = $this->getSut(
            pathResolver: $this->createPathResolverStub(),
            writerFactory: $this->createWriterFactoryStub()
        );

        $this->expectException(InvalidFileFormatException::class);
        $this->expectExceptionMessage('Cannot write CSV file');

        $sut->exportToCsv($data, $filename, $mapperStub);
    }

    private function createPathResolverStub(): PathResolverInterface
    {
        $pathResolverStub = $this->createStub(PathResolverInterface::class);
        $pathResolverStub->method('getAbsolutePath')
            ->willReturnCallback(fn(string $path) => $this->fileSystem->url() . '/' . $path);

        return $pathResolverStub;
    }

    private function createWriterFactoryStub(): CsvWriterFactoryInterface
    {
        $writerFactoryStub = $this->createStub(CsvWriterFactoryInterface::class);
        $writerFactoryStub->method('create')
            ->willReturnCallback(fn(string $path) => Writer::from($path, 'w'));

        return $writerFactoryStub;
    }

    private function getSut(
        ?PathResolverInterface $pathResolver = null,
        ?CsvWriterFactoryInterface $writerFactory = null,
    ): CsvExportServiceInterface {
        $pathResolver ??= $this->createStub(PathResolverInterface::class);
        $writerFactory ??= $this->createStub(CsvWriterFactoryInterface::class);

        return new CsvExportService(
            pathResolver: $pathResolver,
            writerFactory: $writerFactory
        );
    }
}
