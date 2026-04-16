<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Service;

use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Enum\CredentialStatus;

interface PasswordHashAnalyzerInterface
{
    public function getStatus(string $passwordHash): CredentialStatus;

    public function getAlgorithm(string $passwordHash): string;
}
