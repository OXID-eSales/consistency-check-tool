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
    public function moveFileSuccessfully(): void
    {
        $sut = $this->getSut();
        $sourceDir = vfsStream::newDirectory(uniqid())->at($this->fileSystem);
        $destinationDir = vfsStream::newDirectory(uniqid())->at($this->fileSystem);

        $sourceFile = vfsStream::newFile($testFile = uniqid())->at($sourceDir);
        $sourcePath = $sourceFile->url();
        $destinationPath = $destinationDir->url() . '/' . $testFile;

        $sut->moveFile($sourcePath, $destinationPath);

        $this->assertFalse(file_exists($sourcePath));
        $this->assertTrue(file_exists($destinationPath));
    }

    #[Test]
    public function moveNonExistingFileThrowsException(): void
    {
        $sut = $this->getSut();

        $this->expectException(FileSystemException::class);
        $this->expectExceptionMessage(sprintf(FileSystemException::FILE_NOT_FOUND, $nonExistingFile = uniqid()));

        $sut->moveFile($nonExistingFile, uniqid());
    }

    #[Test]
    public function moveFileThrowsExceptionWhenRenameFails(): void
    {
        $sut = $this->getSut();

        $sourceDir = vfsStream::newDirectory(uniqid())->at($this->fileSystem);
        $destinationDir = vfsStream::newDirectory(uniqid(), 0555)->at($this->fileSystem);

        $sourceFile = vfsStream::newFile($testFile = uniqid())->at($sourceDir);
        $sourcePath = $sourceFile->url();
        $destinationPath = $destinationDir->url() . '/' . $testFile;

        $this->expectException(FileSystemException::class);
        $this->expectExceptionMessage(sprintf(FileSystemException::FILE_MOVE_FAILED, $sourcePath, $destinationPath));

        $sut->moveFile($sourcePath, $destinationPath);
    }

    #[Test]
    public function deleteFileSuccessfully(): void
    {
        $sut = $this->getSut();
        $file = vfsStream::newFile(uniqid())->at($this->fileSystem);

        $sut->deleteFile($file->url());

        $this->assertFalse(file_exists($file->url()));
    }

    #[Test]
    public function deleteNonExistingFileThrowsException(): void
    {
        $sut = $this->getSut();

        $this->expectException(FileSystemException::class);
        $this->expectExceptionMessage(sprintf(FileSystemException::FILE_NOT_FOUND, $nonExistingFile = uniqid()));

        $sut->deleteFile($nonExistingFile);
    }

    #[Test]
    public function testDeleteFileThrowsExceptionWhenUnlinkFails(): void
    {
        vfsStream::newFile(uniqid(), 0000)
            ->withContent(uniqid())
            ->at($this->fileSystem);
        $file = $this->fileSystem->url();

        $this->expectException(FileSystemException::class);
        $this->expectExceptionMessage(sprintf(FileSystemException::FILE_DELETE_FAILED, $file));

        $sut = $this->getSut();

        $sut->deleteFile($file);
    }

    private function getSut(): FileSystemUtilsInterface
    {
        return new FileSystemUtils(new Finder());
    }
}
