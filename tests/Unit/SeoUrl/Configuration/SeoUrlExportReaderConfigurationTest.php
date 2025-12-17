<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Configuration;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportReaderConfigurationInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Configuration\SeoUrlExportReaderConfiguration;
use OxidEsales\ConsistencyCheck\SeoUrl\Factory\SeoUrlDtoFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoUrlExportReaderConfigurationTest extends TestCase
{
    #[Test]
    public function getFilePath(): void
    {
        $filePath = uniqid();
        $sut = $this->getSut(filePath: $filePath);

        $this->assertSame($filePath, $sut->getFilePath());
    }

    #[Test]
    public function getDtoFactory(): void
    {
        $dtoFactoryStub = $this->createStub(SeoUrlDtoFactoryInterface::class);
        $sut = $this->getSut(dtoFactory: $dtoFactoryStub);

        $this->assertSame($dtoFactoryStub, $sut->getDtoFactory());
    }

    private function getSut(
        ?string $filePath = null,
        ?SeoUrlDtoFactoryInterface $dtoFactory = null,
    ): ExportReaderConfigurationInterface {
        $filePath ??= uniqid();
        $dtoFactory ??= $this->createStub(SeoUrlDtoFactoryInterface::class);

        return new SeoUrlExportReaderConfiguration($filePath, $dtoFactory);
    }
}
