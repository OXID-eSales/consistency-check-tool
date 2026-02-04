<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Infrastructure\Factory;

use OxidEsales\ConsistencyCheck\Shared\Service\PathResolverInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;

final class MonologConfigurationFactory implements MonologConfigurationFactoryInterface
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
    ) {
    }

    public function create(
        string $loggerName,
        string $logFilePath,
        string $logLevel,
    ): MonologConfigurationInterface {
        return new MonologConfiguration(
            $loggerName,
            $this->pathResolver->getAbsolutePath($logFilePath),
            $logLevel,
        );
    }
}
