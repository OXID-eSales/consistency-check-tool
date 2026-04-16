<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Exception;

final class FilterNotFoundException extends \RuntimeException
{
    /**
     * @param array<string> $availableFilters
     */
    public function __construct(string $filterName, array $availableFilters)
    {
        $message = sprintf(
            'Filter "%s" not found. Available filters: %s',
            $filterName,
            implode(', ', $availableFilters) ?: 'none'
        );

        parent::__construct($message);
    }
}
