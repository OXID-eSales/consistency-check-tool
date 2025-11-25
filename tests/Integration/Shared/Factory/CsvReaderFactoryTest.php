<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\Shared\Factory;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\Shared\Factory\CsvReaderFactory;
use OxidEsales\ConsistencyCheck\Shared\Factory\CsvReaderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class CsvReaderFactoryTest extends IntegrationTestCase
{
    private vfsStreamDirectory $fileSystem;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileSystem = vfsStream::setup('root');
    }

    #[Test]
    public function createReturnsUniqueReaderInstances(): void
    {
        $filename1 = uniqid() . '.csv';
        $filename2 = uniqid() . '.csv';
        $csvContent = "col1,col2\nvalue1,value2\n";

        vfsStream::newFile($filename1)->withContent($csvContent)->at($this->fileSystem);
        vfsStream::newFile($filename2)->withContent($csvContent)->at($this->fileSystem);

        $filepath1 = $this->fileSystem->url() . '/' . $filename1;
        $filepath2 = $this->fileSystem->url() . '/' . $filename2;

        $sut = $this->getSut();
        $reader1 = $sut->create($filepath1);
        $reader2 = $sut->create($filepath2);

        $this->assertNotSame($reader1, $reader2);
    }

    private function getSut(): CsvReaderFactoryInterface
    {
        return new CsvReaderFactory();
    }
}
