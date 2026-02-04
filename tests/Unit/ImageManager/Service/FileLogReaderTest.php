<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ImageManager\Service;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\ImageManager\Service\FileLogReader;
use OxidEsales\ConsistencyCheck\ImageManager\Service\LogReaderInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FileLogReaderTest extends TestCase
{
    private vfsStreamDirectory $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = vfsStream::setup('root');
    }

    #[Test]
    public function itReturnsLogLinesIfFileExists(): void
    {
        $logFilePath = uniqid();
        $line2 = uniqid();
        $line3 = uniqid();
        $file = vfsStream::newFile(uniqid())
            ->withContent("\n$line2\n$line3\n")
            ->at($this->fileSystem);

        $pathResolverMock = $this->createMock(PathResolverInterface::class);
        $pathResolverMock->method('getAbsolutePath')
            ->with($logFilePath)
            ->willReturn($file->url());

        $sut = $this->getSut($logFilePath, $pathResolverMock);

        $lines = $sut->readLines();

        $this->assertCount(2, $lines);
        $this->assertSame($line2, $lines[0]);
        $this->assertSame($line3, $lines[1]);
    }

    #[Test]
    public function itReturnsEmptyArrayIfFileDoesNotExist(): void
    {
        $logFilePath = uniqid();
        $absolutePath = $this->fileSystem->url() . '/' . uniqid();

        $pathResolverMock = $this->createMock(PathResolverInterface::class);
        $pathResolverMock->method('getAbsolutePath')
            ->with($logFilePath)
            ->willReturn($absolutePath);

        $sut = $this->getSut($logFilePath, $pathResolverMock);

        $this->assertSame([], $sut->readLines());
    }

    #[Test]
    public function itSkipsEmptyLines(): void
    {
        $logFilePath = uniqid();
        $line1 = uniqid();
        $line2 = uniqid();
        $file = vfsStream::newFile(uniqid())
            ->withContent("$line1\n\n$line2\n\n")
            ->at($this->fileSystem);

        $pathResolverMock = $this->createMock(PathResolverInterface::class);
        $pathResolverMock->method('getAbsolutePath')
            ->with($logFilePath)
            ->willReturn($file->url());

        $sut = $this->getSut($logFilePath, $pathResolverMock);

        $lines = $sut->readLines();

        $this->assertCount(2, $lines);
        $this->assertSame($line1, $lines[0]);
        $this->assertSame($line2, $lines[1]);
    }

    private function getSut(
        string $logFilePath,
        ?PathResolverInterface $pathResolver = null,
    ): LogReaderInterface {
        return new FileLogReader(
            $pathResolver ?? $this->createStub(PathResolverInterface::class),
            $logFilePath,
        );
    }
}
