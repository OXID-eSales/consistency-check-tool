<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Export\Service;

use League\Csv\Writer;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
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
    private vfsStreamDirectory $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = vfsStream::setup('root');
    }

    #[Test]
    public function export(): void
    {
        $value1 = uniqid();
        $value2 = uniqid();
        $value3 = uniqid();
        $value4 = uniqid();

        $dto1 = $this->createStub(ExportableDtoInterface::class);
        $dto2 = $this->createStub(ExportableDtoInterface::class);

        $arrayFactoryStub = $this->createStub(ArrayFactoryInterface::class);
        $arrayFactoryStub->method('createFromDto')->willReturnCallback(
            fn($dto) => $dto === $dto1
                ? ['field1' => $value1, 'field2' => $value2]
                : ['field1' => $value3, 'field2' => $value4]
        );

        $configurationStub = $this->createConfiguredStub(ExportConfigurationInterface::class, [
            'getHeaders' => ['field1', 'field2'],
            'getItems' => [$dto1, $dto2],
            'getArrayFactory' => $arrayFactoryStub,
            'getFilePath' => $filepath = uniqid() . '.csv',
        ]);

        $sut = $this->getSut(
            pathResolver: $this->createPathResolverStub(),
            writerFactory: $this->createWriterFactoryStub()
        );

        $sut->export($configurationStub);

        $content = file_get_contents($this->fileSystem->url() . '/' . $filepath);
        $this->assertStringContainsString('field1', $content);
        $this->assertStringContainsString($value1, $content);
        $this->assertStringContainsString($value2, $content);
        $this->assertStringContainsString($value3, $content);
        $this->assertStringContainsString($value4, $content);
    }

    #[Test]
    public function exportThrowsExceptionWhenFileCannotBeOpened(): void
    {
        $dto = $this->createStub(ExportableDtoInterface::class);

        $arrayFactoryStub = $this->createStub(ArrayFactoryInterface::class);
        $arrayFactoryStub->method('createFromDto')->willReturn(['col1' => 'value1']);

        $configurationStub = $this->createConfiguredStub(ExportConfigurationInterface::class, [
            'getHeaders' => ['col1'],
            'getItems' => [$dto],
            'getArrayFactory' => $arrayFactoryStub,
            'getFilePath' => $filename = uniqid() . '.csv',
        ]);

        // Create a directory where file should be (blocks file creation)
        vfsStream::newDirectory($filename)->at($this->fileSystem);

        $sut = $this->getSut(
            pathResolver: $this->createPathResolverStub(),
            writerFactory: $this->createWriterFactoryStub()
        );

        $this->expectException(CsvExportException::class);
        $this->expectExceptionMessage($filename);

        $sut->export($configurationStub);
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
    ): ExportServiceInterface {
        $pathResolver ??= $this->createStub(PathResolverInterface::class);
        $writerFactory ??= $this->createStub(CsvWriterFactoryInterface::class);

        return new CsvExportService(
            pathResolver: $pathResolver,
            writerFactory: $writerFactory
        );
    }
}
