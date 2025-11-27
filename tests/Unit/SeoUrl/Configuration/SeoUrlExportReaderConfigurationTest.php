<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Configuration;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportReaderConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Configuration\SeoUrlExportReaderConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoUrlExportReaderConfigurationTest extends TestCase
{
    #[Test]
    public function getFilePath(): void
    {
        $filePath = uniqid() . '.csv';
        $sut = $this->getSut(filePath: $filePath);

        $this->assertSame($filePath, $sut->getFilePath());
    }

    #[Test]
    public function getDtoFactory(): void
    {
        $dtoFactoryStub = $this->createStub(DtoFactoryInterface::class);
        $sut = $this->getSut(dtoFactory: $dtoFactoryStub);

        $this->assertSame($dtoFactoryStub, $sut->getDtoFactory());
    }

    private function getSut(
        ?string $filePath = null,
        ?DtoFactoryInterface $dtoFactory = null,
    ): ExportReaderConfigurationInterface {
        $filePath ??= uniqid() . '.csv';
        $dtoFactory ??= $this->createStub(DtoFactoryInterface::class);

        return new SeoUrlExportReaderConfiguration($filePath, $dtoFactory);
    }
}
