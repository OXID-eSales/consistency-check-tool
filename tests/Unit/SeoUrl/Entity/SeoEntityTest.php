<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Entity;

use OxidEsales\ConsistencyCheck\SeoUrl\Entity\SeoEntity;
use OxidEsales\ConsistencyCheck\SeoUrl\Entity\SeoEntityInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoEntityTest extends TestCase
{
    #[Test]
    public function getSeoType(): void
    {
        $seoType = uniqid();
        $referenceTable = uniqid();

        $sut = $this->getSut($seoType, $referenceTable);

        $this->assertSame($seoType, $sut->getSeoType());
    }

    #[Test]
    public function getReferenceTable(): void
    {
        $seoType = uniqid();
        $referenceTable = uniqid();

        $sut = $this->getSut($seoType, $referenceTable);

        $this->assertSame($referenceTable, $sut->getReferenceTable());
    }

    #[Test]
    public function acceptsNullReferenceTable(): void
    {
        $seoType = uniqid();

        $sut = $this->getSut($seoType, null);

        $this->assertSame($seoType, $sut->getSeoType());
        $this->assertNull($sut->getReferenceTable());
    }

    private function getSut(string $seoType, ?string $referenceTable): SeoEntityInterface
    {
        return new SeoEntity($seoType, $referenceTable);
    }
}
