<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ExportByFilter\Filter\Credentials\Service;

use Generator;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Enum\CredentialStatus;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Service\PasswordHashAnalyzer;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Service\PasswordHashAnalyzerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PasswordHashAnalyzerTest extends TestCase
{
    #[Test]
    #[DataProvider('getStatusDataProvider')]
    public function getStatus(string $hash, CredentialStatus $expectedStatus): void
    {
        $sut = $this->getSut();

        $result = $sut->getStatus($hash);

        $this->assertSame($expectedStatus, $result);
    }

    public static function getStatusDataProvider(): Generator
    {
        yield 'bcrypt $2y$ variant' => [
            '$2y$10$N9qo8uLOickgx2ZMRZoMy.MqrqCh0h8bVN.Bv.X6e9jKm9hIcyqHu',
            CredentialStatus::SUPPORTED,
        ];
        yield 'bcrypt $2a$ variant' => [
            '$2a$10$N9qo8uLOickgx2ZMRZoMy.MqrqCh0h8bVN.Bv.X6e9jKm9hIcyqHu',
            CredentialStatus::SUPPORTED,
        ];
        yield 'bcrypt $2b$ variant' => [
            '$2b$10$N9qo8uLOickgx2ZMRZoMy.MqrqCh0h8bVN.Bv.X6e9jKm9hIcyqHu',
            CredentialStatus::SUPPORTED,
        ];
        yield 'sha512 lowercase' => [
            str_repeat('a1b2c3d4', 16),
            CredentialStatus::DEPRECATED,
        ];
        yield 'sha512 mixed case' => [
            'A1B2C3D4e5f6a7b8' . str_repeat('0', 112),
            CredentialStatus::DEPRECATED,
        ];
        yield 'md5 lowercase' => [
            'e10adc3949ba59abbe56e057f20f883e',
            CredentialStatus::UNSUPPORTED,
        ];
        yield 'md5 uppercase' => [
            'E10ADC3949BA59ABBE56E057F20F883E',
            CredentialStatus::UNSUPPORTED,
        ];
        yield 'empty hash' => [
            '',
            CredentialStatus::NOT_SET,
        ];
        yield 'unknown format' => [
            'some-random-invalid-hash-format',
            CredentialStatus::UNKNOWN,
        ];
    }

    #[Test]
    #[DataProvider('getAlgorithmDataProvider')]
    public function getAlgorithm(string $hash, string $expectedAlgorithm): void
    {
        $sut = $this->getSut();

        $result = $sut->getAlgorithm($hash);

        $this->assertSame($expectedAlgorithm, $result);
    }

    public static function getAlgorithmDataProvider(): Generator
    {
        yield 'bcrypt $2y$ variant' => [
            '$2y$10$N9qo8uLOickgx2ZMRZoMy.MqrqCh0h8bVN.Bv.X6e9jKm9hIcyqHu',
            'bcrypt',
        ];
        yield 'bcrypt $2a$ variant' => [
            '$2a$10$N9qo8uLOickgx2ZMRZoMy.MqrqCh0h8bVN.Bv.X6e9jKm9hIcyqHu',
            'bcrypt',
        ];
        yield 'bcrypt $2b$ variant' => [
            '$2b$10$N9qo8uLOickgx2ZMRZoMy.MqrqCh0h8bVN.Bv.X6e9jKm9hIcyqHu',
            'bcrypt',
        ];
        yield 'sha512' => [
            str_repeat('a1b2c3d4', 16),
            'sha512',
        ];
        yield 'md5' => [
            'e10adc3949ba59abbe56e057f20f883e',
            'md5',
        ];
        yield 'empty hash' => [
            '',
            'none',
        ];
        yield 'unknown format' => [
            'some-random-invalid-hash-format',
            'unknown',
        ];
    }

    private function getSut(): PasswordHashAnalyzerInterface
    {
        return new PasswordHashAnalyzer();
    }
}
