<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Exception;

use OxidEsales\ConsistencyCheck\SeoUrl\Exception\MissingSuffixException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MissingSuffixExceptionTest extends TestCase
{
    #[Test]
    public function itIsRuntimeException(): void
    {
        $sut = new MissingSuffixException();

        $this->assertInstanceOf(\RuntimeException::class, $sut);
    }

    #[Test]
    public function itHasMessage(): void
    {
        $sut = new MissingSuffixException();

        $this->assertNotEmpty($sut->getMessage());
    }
}
