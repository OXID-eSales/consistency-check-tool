<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ExportByFilter\Factory;

use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;
use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Factory\ExportConfigurationFactory;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\FilterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExportConfigurationFactory::class)]
final class ExportConfigurationFactoryTest extends TestCase
{
    #[Test]
    public function createFromFilter(): void
    {
        $items = [$this->createStub(ExportableDtoInterface::class)];
        $headers = ['col1', 'col2'];
        $arrayFactoryStub = $this->createStub(ArrayFactoryInterface::class);
        $filterName = uniqid();

        $filterStub = $this->createConfiguredStub(FilterInterface::class, [
            'getItems' => $items,
            'getHeaders' => $headers,
            'getArrayFactory' => $arrayFactoryStub,
            'getName' => $filterName,
        ]);

        $sut = new ExportConfigurationFactory();

        $result = $sut->createFromFilter($filterStub);

        $this->assertSame($items, $result->getItems());
        $this->assertSame($headers, $result->getHeaders());
        $this->assertSame($arrayFactoryStub, $result->getArrayFactory());
        $this->assertSame($filterName, $result->getFilePrefix());
    }
}
