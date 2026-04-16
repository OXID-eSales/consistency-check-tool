<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory;

use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDtoInterface;

interface UserCredentialDtoFactoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): UserCredentialDtoInterface;
}
