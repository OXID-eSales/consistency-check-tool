<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Factory;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportReaderConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Configuration\SeoUrlExportReaderConfiguration;
use OxidEsales\ConsistencyCheck\SeoUrl\Factory\SeoUrlExportReaderConfigurationFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoUrlExportReaderConfigurationFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        $filePath = uniqid() . '.csv';
        $dtoFactoryStub = $this->createStub(DtoFactoryInterface::class);

        $sut = new SeoUrlExportReaderConfigurationFactory($dtoFactoryStub);
        $result = $sut->create($filePath);

        $this->assertInstanceOf(ExportReaderConfigurationInterface::class, $result);
        $this->assertInstanceOf(SeoUrlExportReaderConfiguration::class, $result);
        $this->assertSame($filePath, $result->getFilePath());
        $this->assertSame($dtoFactoryStub, $result->getDtoFactory());
    }

    #[Test]
    public function createReturnsUniqueInstances(): void
    {
        $filePath1 = uniqid() . '.csv';
        $filePath2 = uniqid() . '.csv';
        $dtoFactoryStub = $this->createStub(DtoFactoryInterface::class);

        $sut = new SeoUrlExportReaderConfigurationFactory($dtoFactoryStub);
        $result1 = $sut->create($filePath1);
        $result2 = $sut->create($filePath2);

        $this->assertNotSame($result1, $result2);
        $this->assertSame($filePath1, $result1->getFilePath());
        $this->assertSame($filePath2, $result2->getFilePath());
    }

    #[Test]
    public function createPreservesDtoFactory(): void
    {
        $filePath = uniqid() . '.csv';
        $dtoFactoryStub = $this->createStub(DtoFactoryInterface::class);

        $sut = new SeoUrlExportReaderConfigurationFactory($dtoFactoryStub);
        $result = $sut->create($filePath);

        $this->assertSame($dtoFactoryStub, $result->getDtoFactory());
    }
}
