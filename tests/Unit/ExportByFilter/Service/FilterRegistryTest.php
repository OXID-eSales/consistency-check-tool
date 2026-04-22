<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ExportByFilter\Service;

use OxidEsales\ConsistencyCheck\ExportByFilter\Exception\FilterNotFoundException;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\FilterInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Service\FilterRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterRegistry::class)]
final class FilterRegistryTest extends TestCase
{
    #[Test]
    public function get(): void
    {
        $filterName = uniqid();
        $filterStub = $this->createStub(FilterInterface::class);
        $filterStub->method('getName')->willReturn($filterName);

        $this->assertSame($filterStub, $this->getSut([$filterStub])->get($filterName));
    }

    #[Test]
    public function getThrowsExceptionForUnknownFilter(): void
    {
        $filterName = uniqid();

        $this->expectException(FilterNotFoundException::class);
        $this->expectExceptionMessage(
            (new FilterNotFoundException($filterName, []))->getMessage()
        );

        $this->getSut([])->get($filterName);
    }

    #[Test]
    public function getAvailableFilters(): void
    {
        $filterName = uniqid();
        $filterStub = $this->createStub(FilterInterface::class);
        $filterStub->method('getName')->willReturn($filterName);

        $this->assertSame([$filterName], $this->getSut([$filterStub])->getAvailableFilters());
    }

    /**
     * @param iterable<FilterInterface> $filters
     */
    private function getSut(iterable $filters): FilterRegistry
    {
        return new FilterRegistry($filters);
    }
}
