<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory;

use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDto;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDtoInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Service\PasswordHashAnalyzerInterface;

final class UserCredentialDtoFactory implements UserCredentialDtoFactoryInterface
{
    public function __construct(
        private readonly PasswordHashAnalyzerInterface $passwordHashAnalyzer,
    ) {
    }

    public function createFromArray(array $data): UserCredentialDtoInterface
    {
        return new UserCredentialDto(
            userId: $data['OXID'],
            active: (bool)$data['OXACTIVE'],
            createdAt: $data['OXCREATE'],
            userUpdatedAt: $data['OXTIMESTAMP'],
            lastOrderAt: $data['OXLASTORDER'],
            credentialStatus: $this->passwordHashAnalyzer->getStatus($data['OXPASSWORD']),
            credentialHashScheme: $this->passwordHashAnalyzer->getAlgorithm($data['OXPASSWORD']),
        );
    }
}
