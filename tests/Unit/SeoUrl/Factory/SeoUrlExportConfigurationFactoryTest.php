<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Factory;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Configuration\SeoUrlExportConfiguration;
use OxidEsales\ConsistencyCheck\SeoUrl\Factory\SeoUrlExportConfigurationFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoUrlExportConfigurationFactoryTest extends TestCase
{
    #[Test]
    public function create(): void
    {
        $filePath = uniqid() . '.csv';
        $arrayFactoryStub = $this->createStub(ArrayFactoryInterface::class);

        $sut = new SeoUrlExportConfigurationFactory($arrayFactoryStub);
        $result = $sut->create([], $filePath);

        $this->assertInstanceOf(ExportConfigurationInterface::class, $result);
        $this->assertInstanceOf(SeoUrlExportConfiguration::class, $result);
        $this->assertSame($filePath, $result->getFilePath());
    }

    #[Test]
    public function createReturnsUniqueInstances(): void
    {
        $filePath1 = uniqid() . '.csv';
        $filePath2 = uniqid() . '.csv';
        $arrayFactoryStub = $this->createStub(ArrayFactoryInterface::class);

        $sut = new SeoUrlExportConfigurationFactory($arrayFactoryStub);
        $result1 = $sut->create([], $filePath1);
        $result2 = $sut->create([], $filePath2);

        $this->assertNotSame($result1, $result2);
        $this->assertSame($filePath1, $result1->getFilePath());
        $this->assertSame($filePath2, $result2->getFilePath());
    }
}
