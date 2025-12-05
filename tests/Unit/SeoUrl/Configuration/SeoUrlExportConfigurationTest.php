<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Configuration;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Configuration\SeoUrlExportConfiguration;
use OxidEsales\ConsistencyCheck\Shared\Dto\ExportableDtoInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoUrlExportConfigurationTest extends TestCase
{
    #[Test]
    public function getHeaders(): void
    {
        $sut = $this->getSut();

        $headers = $sut->getHeaders();

        $expectedHeaders = [
            'OXOBJECTID',
            'OXIDENT',
            'OXSHOPID',
            'OXLANG',
            'OXSTDURL',
            'OXSEOURL',
            'OXTYPE',
            'OXFIXED',
            'OXEXPIRED',
            'OXPARAMS',
            'OXTIMESTAMP',
        ];

        $this->assertSame($expectedHeaders, $headers);
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
    public function getFilePath(): void
    {
        $filePath = uniqid();
        $sut = $this->getSut(filePath: $filePath);

        $this->assertSame($filePath, $sut->getFilePath());
    }

    private function getSut(
        array $items = [],
        ?ArrayFactoryInterface $arrayFactory = null,
        ?string $filePath = null,
    ): ExportConfigurationInterface {
        $arrayFactory ??= $this->createStub(ArrayFactoryInterface::class);
        $filePath ??= uniqid();

        return new SeoUrlExportConfiguration($items, $arrayFactory, $filePath);
    }
}
