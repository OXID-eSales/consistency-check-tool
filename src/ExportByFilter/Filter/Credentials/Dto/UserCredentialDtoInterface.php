<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto;

use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Enum\CredentialStatus;

interface UserCredentialDtoInterface extends ExportableDtoInterface
{
    public function getUserId(): string;

    public function isActive(): bool;

    public function getCreatedAt(): string;

    public function getUserUpdatedAt(): string;

    public function getLastOrderAt(): string;

    public function getCredentialStatus(): CredentialStatus;

    public function getCredentialHashScheme(): string;
}
