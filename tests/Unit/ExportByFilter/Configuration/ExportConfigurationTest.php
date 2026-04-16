<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ExportByFilter\Configuration;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Configuration\ExportConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExportConfigurationTest extends TestCase
{
    #[Test]
    public function getHeaders(): void
    {
        $headers = [uniqid(), uniqid(), uniqid()];
        $sut = $this->getSut(headers: $headers);

        $this->assertSame($headers, $sut->getHeaders());
    }

    #[Test]
    public function getItems(): void
    {
        $dto1 = $this->createStub(ExportableDtoInterface::class);
        $dto2 = $this->createStub(ExportableDtoInterface::class);
        $items = [$dto1, $dto2];

        $sut = $this->getSut(items: $items);

        $this->assertSame($items, $sut->getItems());
    }

    #[Test]
    public function getArrayFactory(): void
    {
        $arrayFactoryStub = $this->createStub(ArrayFactoryInterface::class);
        $sut = $this->getSut(arrayFactory: $arrayFactoryStub);

        $this->assertSame($arrayFactoryStub, $sut->getArrayFactory());
    }

    #[Test]
    public function getFilePrefix(): void
    {
        $filePrefix = uniqid();
        $sut = $this->getSut(filePrefix: $filePrefix);

        $this->assertSame($filePrefix, $sut->getFilePrefix());
    }

    private function getSut(
        array $items = [],
        array $headers = [],
        ?ArrayFactoryInterface $arrayFactory = null,
        ?string $filePrefix = null,
    ): ExportConfigurationInterface {
        $arrayFactory ??= $this->createStub(ArrayFactoryInterface::class);
        $filePrefix ??= uniqid();

        return new ExportConfiguration($items, $headers, $arrayFactory, $filePrefix);
    }
}
