<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration;

use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\EshopCommunity\Tests\TestContainerFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class ServiceAvailabilityTest extends IntegrationTestCase
{
    public static $cachedContainer;

    public static function setUpBeforeClass(): void
    {
        $container = (new TestContainerFactory())->create();
        $container->compile(true);
        self::$cachedContainer = $container;
    }

    public static function servicesProvider(): array
    {
        // phpcs:disable
        return [
            // ExportByFilter
            [\OxidEsales\ConsistencyCheck\ExportByFilter\Service\FilterRegistryInterface::class],
            [\OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Infrastructure\UserRepositoryInterface::class],
            [\OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory\UserCredentialDtoFactoryInterface::class],
            [\OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Service\PasswordHashAnalyzerInterface::class],
            [\OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory\UserCredentialArrayFactory::class],
        ];
        // phpcs:enable
    }

    #[Test]
    #[DataProvider('servicesProvider')]
    public function serviceIsAvailable(string $serviceName): void
    {
        $service = self::$cachedContainer->get($serviceName);
        $this->assertInstanceOf($serviceName, $service);
    }
}
