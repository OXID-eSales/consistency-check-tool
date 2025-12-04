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
        $filePrefix = uniqid();
        $arrayFactoryStub = $this->createStub(ArrayFactoryInterface::class);

        $sut = new SeoUrlExportConfigurationFactory($arrayFactoryStub, $filePrefix);
        $result = $sut->create([]);

        $this->assertInstanceOf(ExportConfigurationInterface::class, $result);
        $this->assertInstanceOf(SeoUrlExportConfiguration::class, $result);
        $this->assertSame($filePrefix, $result->getFilePrefix());
    }
}
