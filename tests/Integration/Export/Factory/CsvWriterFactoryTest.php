<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\Export\Factory;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use OxidEsales\ConsistencyCheck\Export\Factory\CsvWriterFactory;
use OxidEsales\ConsistencyCheck\Export\Factory\CsvWriterFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class CsvWriterFactoryTest extends IntegrationTestCase
{
    private vfsStreamDirectory $fileSystem;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileSystem = vfsStream::setup('root');
    }

    #[Test]
    public function createReturnsUniqueWriterInstances(): void
    {
        $filename1 = uniqid() . '.csv';
        $filename2 = uniqid() . '.csv';

        $filepath1 = $this->fileSystem->url() . '/' . $filename1;
        $filepath2 = $this->fileSystem->url() . '/' . $filename2;

        $sut = $this->getSut();
        $writer1 = $sut->create($filepath1);
        $writer2 = $sut->create($filepath2);

        $this->assertNotSame($writer1, $writer2);
    }

    private function getSut(): CsvWriterFactoryInterface
    {
        return new CsvWriterFactory();
    }
}
