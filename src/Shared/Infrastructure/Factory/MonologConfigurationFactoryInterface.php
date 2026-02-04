<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Infrastructure\Factory;

use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;

interface MonologConfigurationFactoryInterface
{
    public function create(
        string $loggerName,
        string $logFilePath,
        string $logLevel,
    ): MonologConfigurationInterface;
}
