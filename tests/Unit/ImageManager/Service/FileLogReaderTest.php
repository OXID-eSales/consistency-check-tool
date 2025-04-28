<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Service;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\ImageManager\Service\FileLogReader;
use OxidEsales\ConsistencyCheck\ImageManager\Service\LogReaderInterface;
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
        $line2 = uniqid();
        $line3 = uniqid();
        $file = vfsStream::newFile(uniqid())
            ->withContent("\n$line2\n$line3\n")
            ->at($this->fileSystem);

        $sut = $this->getSut($file->url());

        $lines = $sut->readLines();

        $this->assertCount(2, $lines);
        $this->assertSame($line2, $lines[0]);
        $this->assertSame($line3, $lines[1]);
    }

    #[Test]
    public function itReturnsEmptyArrayIfFileDoesNotExist(): void
    {
        $sut = $this->getSut($this->fileSystem->url() . '/' . uniqid());

        $this->assertSame([], $sut->readLines());
    }

    #[Test]
    public function itSkipsEmptyLines(): void
    {
        $line1 = uniqid();
        $line2 = uniqid();
        $file = vfsStream::newFile(uniqid())
            ->withContent("$line1\n\n$line2\n\n")
            ->at($this->fileSystem);

        $sut = $this->getSut($file->url());

        $lines = $sut->readLines();

        $this->assertCount(2, $lines);
        $this->assertSame($line1, $lines[0]);
        $this->assertSame($line2, $lines[1]);
    }

    private function getSut(string $path): LogReaderInterface
    {
        return new FileLogReader($path);
    }
}
