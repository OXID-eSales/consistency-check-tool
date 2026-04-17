<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory;

use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDtoInterface;

/**
 * @phpstan-type UserTableRow array{
 *     OXID: string,
 *     OXACTIVE: string,
 *     OXCREATE: string,
 *     OXTIMESTAMP: string,
 *     OXLASTORDER: string,
 *     OXPASSWORD: string
 * }
 */
interface UserCredentialDtoFactoryInterface extends DtoFactoryInterface
{
    /**
     * @param UserTableRow $data
     */
    public function createFromArray(array $data): UserCredentialDtoInterface;
}
