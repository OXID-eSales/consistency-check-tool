<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Infrastructure;

use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDtoInterface;

interface UserRepositoryInterface
{
    /**
     * @return array<UserCredentialDtoInterface>
     */
    public function findUsersWithOutdatedCredentials(): array;
}
