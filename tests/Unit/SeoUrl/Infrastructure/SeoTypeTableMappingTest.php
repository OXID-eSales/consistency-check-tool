<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Infrastructure;

use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoTypeTableMapping;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoTypeTableMappingInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoTypeTableMappingTest extends TestCase
{
    #[Test]
    public function getSeoTypeAndReferenceTable(): void
    {
        $seoType = uniqid();
        $referenceTable = uniqid();

        $sut = $this->getSut($seoType, $referenceTable);

        $this->assertSame($seoType, $sut->getSeoType());
        $this->assertSame($referenceTable, $sut->getReferenceTable());
    }

    private function getSut(string $seoType, string $referenceTable): SeoTypeTableMappingInterface
    {
        return new SeoTypeTableMapping($seoType, $referenceTable);
    }
}
