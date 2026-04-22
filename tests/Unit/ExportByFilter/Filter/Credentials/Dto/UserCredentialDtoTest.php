<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ExportByFilter\Filter\Credentials\Dto;

use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDto;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Enum\CredentialStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserCredentialDtoTest extends TestCase
{
    #[Test]
    public function itReturnsCorrectGetterValues(): void
    {
        $credentialStatusCases = CredentialStatus::cases();
        $expectedCredentialStatus = $credentialStatusCases[array_rand($credentialStatusCases)];

        $sut = new UserCredentialDto(
            userId: $expectedUserId = uniqid(),
            active: $expectedActive = (bool)rand(0, 1),
            createdAt: $expectedCreatedAt = date('Y-m-d H:i:s'),
            userUpdatedAt: $expectedUserUpdatedAt = date('Y-m-d H:i:s'),
            lastOrderAt: $expectedLastOrderAt = date('Y-m-d H:i:s'),
            credentialStatus: $expectedCredentialStatus,
            credentialHashScheme: $expectedCredentialHashScheme = uniqid(),
        );

        $this->assertSame($expectedUserId, $sut->getUserId());
        $this->assertSame($expectedActive, $sut->isActive());
        $this->assertSame($expectedCreatedAt, $sut->getCreatedAt());
        $this->assertSame($expectedUserUpdatedAt, $sut->getUserUpdatedAt());
        $this->assertSame($expectedLastOrderAt, $sut->getLastOrderAt());
        $this->assertSame($expectedCredentialStatus, $sut->getCredentialStatus());
        $this->assertSame($expectedCredentialHashScheme, $sut->getCredentialHashScheme());
    }
}
