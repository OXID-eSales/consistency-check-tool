<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use OxidEsales\ConsistencyCheck\ImageManager\Exception\DirectoryNotFoundException;

class DirectoryNotFoundExceptionTest extends TestCase
{
    #[Test]
    public function itsThrowable(): void
    {
        $sut = new DirectoryNotFoundException(uniqid());

        $this->assertInstanceOf(\Throwable::class, $sut);
    }

    #[Test]
    public function exceptionMessageMentionsRequiredField(): void
    {
        $sut = new DirectoryNotFoundException($directoryPath = uniqid());

        $this->assertStringContainsString($directoryPath, $sut->getMessage());
    }
}
