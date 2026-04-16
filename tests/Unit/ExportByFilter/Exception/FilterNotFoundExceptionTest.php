<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ExportByFilter\Exception;

use OxidEsales\ConsistencyCheck\ExportByFilter\Exception\FilterNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class FilterNotFoundExceptionTest extends TestCase
{
    #[Test]
    public function exceptionMessageWithAvailableFilters(): void
    {
        $filterName = uniqid();
        $availableFilters = [uniqid(), uniqid(), uniqid()];

        $sut = new FilterNotFoundException($filterName, $availableFilters);

        $expectedMessage = sprintf(
            'Filter "%s" not found. Available filters: %s',
            $filterName,
            implode(', ', $availableFilters)
        );

        $this->assertSame($expectedMessage, $sut->getMessage());
    }

    #[Test]
    public function exceptionMessageWithNoAvailableFilters(): void
    {
        $filterName = uniqid();
        $availableFilters = [];

        $sut = new FilterNotFoundException($filterName, $availableFilters);

        $expectedMessage = sprintf(
            'Filter "%s" not found. Available filters: none',
            $filterName
        );

        $this->assertSame($expectedMessage, $sut->getMessage());
    }
}
