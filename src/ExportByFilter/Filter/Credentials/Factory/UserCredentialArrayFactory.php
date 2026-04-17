<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory;

use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;
use OxidEsales\ConsistencyCheck\Export\Exception\InvalidDtoTypeException;
use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDtoInterface;

final class UserCredentialArrayFactory implements ArrayFactoryInterface
{
    public function createFromDto(ExportableDtoInterface $dto): array
    {
        if (!$dto instanceof UserCredentialDtoInterface) {
            throw new InvalidDtoTypeException($dto, UserCredentialDtoInterface::class);
        }

        return [
            'user_id' => $dto->getUserId(),
            'active' => $dto->isActive(),
            'created_at' => $dto->getCreatedAt(),
            'user_updated_at' => $dto->getUserUpdatedAt(),
            'last_order_at' => $dto->getLastOrderAt(),
            'credential_status' => $dto->getCredentialStatus()->value,
            'credential_hash_scheme' => $dto->getCredentialHashScheme(),
        ];
    }
}
