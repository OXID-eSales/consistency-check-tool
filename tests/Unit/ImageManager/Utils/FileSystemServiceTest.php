<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Utils;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\ImageManager\Exception\FileSystemException;
use OxidEsales\ConsistencyCheck\ImageManager\Utils\FileSystemUtils;
use OxidEsales\ConsistencyCheck\ImageManager\Utils\FileSystemUtilsInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class FileSystemServiceTest extends TestCase
{
    private vfsStreamDirectory $fileSystem;

    protected function setUp(): void
    {
        $this->fileSystem = vfsStream::setup();
    }

    #[Test]
    public function directoryExistsReturnsBooleanResponse(): void
    {
        $sut = $this->getSut();
        vfsStream::newDirectory($existingDirectory = uniqid())->at($this->fileSystem);

        $this->assertTrue($sut->directoryExists($this->fileSystem->url() . '/' . $existingDirectory));
        $this->assertFalse($sut->directoryExists($this->fileSystem->url() . '/' . uniqid()));
    }

    #[Test]
    public function getFilesInEmptyDirectory(): void
    {
        $sut = $this->getSut();
        $emptyDirectory = vfsStream::newDirectory(uniqid())->at($this->fileSystem);

        $actualFiles = $sut->getFilesInDirectory($emptyDirectory->url());

        $this->assertIsArray($actualFiles);
        $this->assertCount(0, $actualFiles);
    }

    #[Test]
    public function getFilesInExistingDirectory(): void
    {
        $expectedDirectory = uniqid();
        $sut = $this->getSut();
        $dir = vfsStream::newDirectory($expectedDirectory)->at($this->fileSystem);

        vfsStream::newFile($expectedFile1 = uniqid())->at($dir);
        vfsStream::newFile($expectedFile2 = uniqid())->at($dir);

        $actualFiles = $sut->getFilesInDirectory($this->fileSystem->url() . '/' . $expectedDirectory);

        $this->assertCount(2, $actualFiles);
        $this->assertContains($expectedFile1, $actualFiles);
        $this->assertContains($expectedFile2, $actualFiles);
    }

    #[Test]
    public function getFilesInNonExistingDirectoryThrowsException(): void
    {
        $sut = $this->getSut();

        $nonExistingDirectory = $this->fileSystem->url() . '/' . uniqid();

        $this->expectException(FileSystemException::class);
        $this->expectExceptionMessage(sprintf(FileSystemException::DIRECTORY_NOT_FOUND, $nonExistingDirectory));

        $sut->getFilesInDirectory($nonExistingDirectory);
    }

    #[Test]
    public function getAbsolutePathReturnsCorrectPath(): void
    {
        $contextStub = $this->createStub(ContextInterface::class);
        $contextStub->method('getSourcePath')->willReturn($basePath = uniqid());

        $sut = $this->getSut($contextStub);

        $relativePath = uniqid();
        $expectedAbsolutePath = $basePath . '/' . $relativePath;

        $this->assertEquals($expectedAbsolutePath, $sut->getAbsolutePath($relativePath));
    }

    private function getSut(
        ?ContextInterface $context = null
    ): FileSystemUtilsInterface {
        $context ??= $this->createStub(ContextInterface::class);
        return new FileSystemUtils(
            finder: new Finder(),
            context: $context,
        );
    }
}
