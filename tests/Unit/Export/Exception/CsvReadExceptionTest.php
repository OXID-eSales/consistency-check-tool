<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Export\Exception;

use OxidEsales\ConsistencyCheck\Export\Exception\CsvReadException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CsvReadExceptionTest extends TestCase
{
    #[DataProvider('throwables')]
    #[Test]
    public function constructorFormatsMessageWithFilepathAndPreviousError(string $throwableClassName): void
    {
        $filepath = uniqid();
        $errorMessage = uniqid();
        $previousException = new $throwableClassName($errorMessage);

        $sut = new CsvReadException($filepath, $previousException);

        $expectedMessage = sprintf('Cannot read CSV file: %s. Error: %s', $filepath, $errorMessage);
        $this->assertSame($expectedMessage, $sut->getMessage());
        $this->assertSame($previousException, $sut->getPrevious());
    }

    public static function throwables(): array
    {
        return [
            [\Exception::class],
            [\Error::class],
            [\TypeError::class],
            [\DivisionByZeroError::class],
            [\RuntimeException::class]
        ];
    }
}
