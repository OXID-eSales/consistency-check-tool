<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Export\Service;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\Export\Exception\ExportDirectoryNotFoundException;
use OxidEsales\ConsistencyCheck\Export\Service\ExportFileNameGenerator;
use OxidEsales\ConsistencyCheck\Export\Service\ExportFileNameGeneratorInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExportFileNameGeneratorTest extends TestCase
{
    private vfsStreamDirectory $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = vfsStream::setup();
    }

    #[Test]
    public function generate(): void
    {
        $exportDirectoryPath = 'export';
        $absoluteExportDirectoryPath = $this->fileSystem->url();

        $pathResolverMock = $this->createMock(PathResolverInterface::class);
        $pathResolverMock->method('getAbsolutePath')
            ->with($exportDirectoryPath)
            ->willReturn($absoluteExportDirectoryPath);

        $sut = $this->getSut(exportDirectoryPath: $exportDirectoryPath, pathResolver: $pathResolverMock);

        $filePrefix = uniqid();
        $fileExtension = uniqid();
        $expectedFileName = sprintf(
            '%s/%s-%s.%s',
            $absoluteExportDirectoryPath,
            $filePrefix,
            date('Y-m-d_H-i-s'),
            $fileExtension
        );

        $result = $sut->generate($filePrefix, $fileExtension);

        $this->assertSame($expectedFileName, $result);
    }

    #[Test]
    public function generateThrowsExceptionWhenDirectoryDoesNotExist(): void
    {
        $exportDirectoryPath = 'non-existent';
        $absoluteExportDirectoryPath = $this->fileSystem->url() . '/' . uniqid();

        $pathResolverMock = $this->createMock(PathResolverInterface::class);
        $pathResolverMock->method('getAbsolutePath')
            ->with($exportDirectoryPath)
            ->willReturn($absoluteExportDirectoryPath);

        $sut = $this->getSut(exportDirectoryPath: $exportDirectoryPath, pathResolver: $pathResolverMock);

        $this->expectException(ExportDirectoryNotFoundException::class);

        $sut->generate(uniqid(), uniqid());
    }

    private function getSut(
        string $exportDirectoryPath,
        ?PathResolverInterface $pathResolver = null,
    ): ExportFileNameGeneratorInterface {
        return new ExportFileNameGenerator(
            $pathResolver ?? $this->createStub(PathResolverInterface::class),
            $exportDirectoryPath,
        );
    }
}
