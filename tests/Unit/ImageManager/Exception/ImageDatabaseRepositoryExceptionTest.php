<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Exception;

use OxidEsales\ConsistencyCheck\ImageManager\Exception\ImageDatabaseRepositoryException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ImageDatabaseRepositoryExceptionTest extends TestCase
{
    #[Test]
    public function itsThrowable(): void
    {
        $sut = new ImageDatabaseRepositoryException(uniqid(), uniqid());

        $this->assertInstanceOf(\Throwable::class, $sut);
    }

    #[Test]
    public function exceptionMessageMentionsRequiredField(): void
    {
        $sut = new ImageDatabaseRepositoryException($fieldName = uniqid(), $tableName = uniqid());

        $this->assertStringContainsString($fieldName, $sut->getMessage());
        $this->assertStringContainsString($tableName, $sut->getMessage());
    }
}
