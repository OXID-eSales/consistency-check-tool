<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Service;

use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Enum\CredentialStatus;

final class PasswordHashAnalyzer implements PasswordHashAnalyzerInterface
{
    private const BCRYPT_PREFIXES = ['$2y$', '$2a$', '$2b$'];
    private const BCRYPT_LENGTH = 60;
    private const SHA512_LENGTH = 128;
    private const MD5_LENGTH = 32;

    public function getStatus(string $passwordHash): CredentialStatus
    {
        return match (true) {
            $passwordHash === '' => CredentialStatus::NOT_SET,
            $this->isBcrypt($passwordHash) => CredentialStatus::SUPPORTED,
            $this->isSha512($passwordHash) => CredentialStatus::DEPRECATED,
            $this->isMd5($passwordHash) => CredentialStatus::UNSUPPORTED,
            default => CredentialStatus::UNKNOWN,
        };
    }

    public function getAlgorithm(string $passwordHash): string
    {
        return match (true) {
            $passwordHash === '' => 'none',
            $this->isBcrypt($passwordHash) => 'bcrypt',
            $this->isSha512($passwordHash) => 'sha512',
            $this->isMd5($passwordHash) => 'md5',
            default => 'unknown',
        };
    }

    private function isBcrypt(string $hash): bool
    {
        if (strlen($hash) !== self::BCRYPT_LENGTH) {
            return false;
        }

        foreach (self::BCRYPT_PREFIXES as $prefix) {
            if (str_starts_with($hash, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function isSha512(string $hash): bool
    {
        return strlen($hash) === self::SHA512_LENGTH && ctype_xdigit($hash);
    }

    private function isMd5(string $hash): bool
    {
        return strlen($hash) === self::MD5_LENGTH && ctype_xdigit($hash);
    }
}
