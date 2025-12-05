<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\Export\Factory;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\Export\Factory\CsvReaderFactory;
use OxidEsales\ConsistencyCheck\Export\Factory\CsvReaderFactoryInterface;
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
        $filename = uniqid() . '.csv';
        $csvContent = "col1,col2\nvalue1,value2\n";

        vfsStream::newFile($filename)->withContent($csvContent)->at($this->fileSystem);

        $filepath = $this->fileSystem->url() . '/' . $filename;

        $sut = $this->getSut();
        $reader1 = $sut->create($filepath);
        $reader2 = $sut->create($filepath);

        $this->assertNotSame($reader1, $reader2);
    }

    private function getSut(): CsvReaderFactoryInterface
    {
        return new CsvReaderFactory();
    }
}
