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
        $sut = $this->getSut($this->fileSystem->url());

        $filePrefix = uniqid();
        $fileExtension = uniqid();
        $expectedFileName = sprintf(
            '%s/%s-%s.%s',
            $this->fileSystem->url(),
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
        $nonExistentDir = $this->fileSystem->url() . '/' . uniqid();
        $sut = $this->getSut($nonExistentDir);

        $this->expectException(ExportDirectoryNotFoundException::class);

        $sut->generate(uniqid(), uniqid());
    }

    private function getSut(string $exportDirectoryPath): ExportFileNameGeneratorInterface
    {
        return new ExportFileNameGenerator($exportDirectoryPath);
    }
}
