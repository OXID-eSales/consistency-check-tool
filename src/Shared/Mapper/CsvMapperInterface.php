<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Mapper;

interface CsvMapperInterface
{
    /**
     * @return array<string> Array of column names
     */
    public function getHeaders(): array;

    /**
     * @param object $dto The DTO object to convert
     * @return array<string, mixed> Associative array with column names as keys
     */
    public function toArray(object $dto): array;

    /**

     * @param array<string, mixed> $data Associative array from CSV row
     * @return object The created DTO object
     */
    public function fromArray(array $data): object;
}
