<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Factory;

use OxidEsales\ConsistencyCheck\Export\Configuration\ExportConfigurationInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Configuration\ExportConfiguration;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\FilterInterface;

final class ExportConfigurationFactory implements ExportConfigurationFactoryInterface
{
    public function createFromFilter(FilterInterface $filter): ExportConfigurationInterface
    {
        return new ExportConfiguration(
            items: $filter->getItems(),
            headers: $filter->getHeaders(),
            arrayFactory: $filter->getArrayFactory(),
            filePrefix: $filter->getName(),
        );
    }
}
