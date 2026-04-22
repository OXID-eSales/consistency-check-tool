<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Service;

use OxidEsales\ConsistencyCheck\ExportByFilter\Exception\FilterNotFoundException;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\FilterInterface;

final class FilterRegistry implements FilterRegistryInterface
{
    /** @var array<string, FilterInterface> */
    private array $filterMap = [];

    /**
     * @param iterable<FilterInterface> $filters
     */
    public function __construct(iterable $filters)
    {
        foreach ($filters as $filter) {
            $this->filterMap[$filter->getName()] = $filter;
        }
    }

    public function get(string $name): FilterInterface
    {
        if (!isset($this->filterMap[$name])) {
            throw new FilterNotFoundException($name, $this->getAvailableFilters());
        }

        return $this->filterMap[$name];
    }

    public function getAvailableFilters(): array
    {
        return array_keys($this->filterMap);
    }
}
