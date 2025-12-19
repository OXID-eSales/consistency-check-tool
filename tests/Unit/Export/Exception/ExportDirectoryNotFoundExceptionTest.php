<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Export\Exception;

use OxidEsales\ConsistencyCheck\Export\Exception\ExportDirectoryNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExportDirectoryNotFoundExceptionTest extends TestCase
{
    #[Test]
    public function exceptionMessageContainsDirectoryPath(): void
    {
        $sut = new ExportDirectoryNotFoundException($directoryPath = uniqid());

        $this->assertStringContainsString($directoryPath, $sut->getMessage());
    }
}
