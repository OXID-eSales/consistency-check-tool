<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Shared\Exception;

use Exception;
use OxidEsales\ConsistencyCheck\Shared\Exception\CsvExportException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsvExportExceptionTest extends TestCase
{
    #[Test]
    public function constructorFormatsMessageWithFilepathAndPreviousError(): void
    {
        $filepath = uniqid() . '.csv';
        $errorMessage = uniqid();
        $previousException = new Exception($errorMessage);

        $sut = new CsvExportException($filepath, $previousException);

        $this->assertStringContainsString($filepath, $sut->getMessage());
        $this->assertStringContainsString($errorMessage, $sut->getMessage());
        $this->assertSame($previousException, $sut->getPrevious());
        $this->assertSame(0, $sut->getCode());
    }
}
