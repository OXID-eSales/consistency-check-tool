<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ExportByFilter\Filter\Credentials\Factory;

use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDtoInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Enum\CredentialStatus;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory\UserCredentialDtoFactory;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory\UserCredentialDtoFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Service\PasswordHashAnalyzerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserCredentialDtoFactoryTest extends TestCase
{
    #[Test]
    public function createFromArray(): void
    {
        $passwordHash = uniqid();
        $credentialStatusCases = CredentialStatus::cases();
        $expectedStatus = $credentialStatusCases[array_rand($credentialStatusCases)];
        $expectedScheme = uniqid();

        $passwordHashAnalyzerMock = $this->createMock(PasswordHashAnalyzerInterface::class);
        $passwordHashAnalyzerMock->method('getStatus')->with($passwordHash)->willReturn($expectedStatus);
        $passwordHashAnalyzerMock->method('getAlgorithm')->with($passwordHash)->willReturn($expectedScheme);

        $expectedActive = (bool)rand(0, 1);
        $data = [
            'OXID' => $expectedUserId = uniqid(),
            'OXACTIVE' => $expectedActive ? '1' : '0',
            'OXCREATE' => $expectedCreatedAt = date('Y-m-d H:i:s', rand()),
            'OXTIMESTAMP' => $expectedUserUpdatedAt = date('Y-m-d H:i:s', rand()),
            'OXLASTORDER' => $expectedLastOrderAt = date('Y-m-d H:i:s', rand()),
            'OXPASSWORD' => $passwordHash,
        ];

        $sut = $this->getSut(passwordHashAnalyzerMock: $passwordHashAnalyzerMock);
        $result = $sut->createFromArray($data);

        $this->assertSame($expectedUserId, $result->getUserId());
        $this->assertSame($expectedActive, $result->isActive());
        $this->assertSame($expectedCreatedAt, $result->getCreatedAt());
        $this->assertSame($expectedUserUpdatedAt, $result->getUserUpdatedAt());
        $this->assertSame($expectedLastOrderAt, $result->getLastOrderAt());
        $this->assertSame($expectedStatus, $result->getCredentialStatus());
        $this->assertSame($expectedScheme, $result->getCredentialHashScheme());
    }

    private function getSut(
        ?PasswordHashAnalyzerInterface $passwordHashAnalyzerMock = null,
    ): UserCredentialDtoFactoryInterface {
        $passwordHashAnalyzerMock ??= $this->createStub(PasswordHashAnalyzerInterface::class);

        return new UserCredentialDtoFactory($passwordHashAnalyzerMock);
    }
}
