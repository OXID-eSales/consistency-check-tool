<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Shared\Infrastructure\Factory;

use OxidEsales\ConsistencyCheck\Shared\Infrastructure\Factory\MonologConfigurationFactory;
use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MonologConfigurationFactoryTest extends TestCase
{
    #[Test]
    public function createReturnsMonologConfigurationWithResolvedPath(): void
    {
        $loggerName = uniqid();
        $logFilePath = uniqid();
        $logLevel = uniqid();
        $absoluteLogFilePath = uniqid();

        $pathResolverMock = $this->createMock(PathResolverInterface::class);
        $pathResolverMock->method('getAbsolutePath')
            ->with($logFilePath)
            ->willReturn($absoluteLogFilePath);

        $sut = $this->getSut($pathResolverMock);

        $result = $sut->create($loggerName, $logFilePath, $logLevel);

        $this->assertSame($loggerName, $result->getLoggerName());
        $this->assertSame($absoluteLogFilePath, $result->getLogFilePath());
        $this->assertSame($logLevel, $result->getLogLevel());
    }

    private function getSut(
        ?PathResolverInterface $pathResolver = null,
    ): MonologConfigurationFactory {
        return new MonologConfigurationFactory(
            $pathResolver ?? $this->createStub(PathResolverInterface::class),
        );
    }
}