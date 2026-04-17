<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Export\Exception;

use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;
use OxidEsales\ConsistencyCheck\Export\Exception\InvalidDtoTypeException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidDtoTypeExceptionTest extends TestCase
{
    #[Test]
    public function exceptionMessage(): void
    {
        $dtoStub = $this->createStub(ExportableDtoInterface::class);
        $expectedType = uniqid();

        $sut = new InvalidDtoTypeException($dtoStub, $expectedType);

        $expectedMessage = sprintf(
            'Invalid DTO type provided. Expected %s, got %s',
            $expectedType,
            get_class($dtoStub)
        );

        $this->assertSame($expectedMessage, $sut->getMessage());
    }
}
