<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ImageManager\Utils;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\ImageManager\Exception\DirectoryNotFoundException;
use OxidEsales\ConsistencyCheck\ImageManager\Utils\FileSystemUtils;
use OxidEsales\ConsistencyCheck\ImageManager\Utils\FileSystemUtilsInterface;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class FileSystemUtilsTest extends TestCase
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

        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage(sprintf("Directory not found: %s", $nonExistingDirectory));

        $sut->getFilesInDirectory($nonExistingDirectory);
    }

    #[Test]
    public function getAbsolutePathReturnsCorrectPath(): void
    {
        $basePath = uniqid();
        $relativePath = uniqid();
        $expectedAbsolutePath = $basePath . '/' . $relativePath;

        $pathResolverStub = $this->createStub(PathResolverInterface::class);
        $pathResolverStub->method('getAbsolutePath')
            ->with($relativePath)
            ->willReturn($expectedAbsolutePath);

        $sut = $this->getSut($pathResolverStub);

        $this->assertEquals($expectedAbsolutePath, $sut->getAbsolutePath($relativePath));
    }

    private function getSut(
        ?PathResolverInterface $pathResolver = null
    ): FileSystemUtilsInterface {
        if ($pathResolver === null) {
            $pathResolver = $this->createStub(PathResolverInterface::class);
            $pathResolver->method('getAbsolutePath')
                ->willReturnCallback(fn(string $path) => $path);
        }

        return new FileSystemUtils(
            finder: new Finder(),
            pathResolver: $pathResolver,
        );
    }
}
