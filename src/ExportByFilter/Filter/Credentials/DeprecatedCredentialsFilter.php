<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials;

use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Infrastructure\UserRepositoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\FilterInterface;

final class DeprecatedCredentialsFilter implements FilterInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ArrayFactoryInterface $arrayFactory,
    ) {
    }

    public function getName(): string
    {
        return 'deprecated-credentials';
    }

    public function getHeaders(): array
    {
        return [
            'user_id',
            'active',
            'created_at',
            'user_updated_at',
            'last_order_at',
            'credential_status',
            'credential_hash_scheme',
        ];
    }

    public function getArrayFactory(): ArrayFactoryInterface
    {
        return $this->arrayFactory;
    }

    public function getItems(): array
    {
        return $this->userRepository->findUsersWithOutdatedCredentials();
    }
}
