<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto;

use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Enum\CredentialStatus;

final class UserCredentialDto implements UserCredentialDtoInterface
{
    public function __construct(
        private readonly string $userId,
        private readonly bool $active,
        private readonly string $createdAt,
        private readonly string $userUpdatedAt,
        private readonly string $lastOrderAt,
        private readonly CredentialStatus $credentialStatus,
        private readonly string $credentialHashScheme,
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUserUpdatedAt(): string
    {
        return $this->userUpdatedAt;
    }

    public function getLastOrderAt(): string
    {
        return $this->lastOrderAt;
    }

    public function getCredentialStatus(): CredentialStatus
    {
        return $this->credentialStatus;
    }

    public function getCredentialHashScheme(): string
    {
        return $this->credentialHashScheme;
    }
}
