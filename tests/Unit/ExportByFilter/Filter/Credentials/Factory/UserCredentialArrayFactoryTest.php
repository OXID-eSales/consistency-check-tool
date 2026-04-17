<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ExportByFilter\Filter\Credentials\Factory;

use OxidEsales\ConsistencyCheck\Export\Dto\ExportableDtoInterface;
use OxidEsales\ConsistencyCheck\Export\Exception\InvalidDtoTypeException;
use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDtoInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Enum\CredentialStatus;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory\UserCredentialArrayFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserCredentialArrayFactoryTest extends TestCase
{
    #[Test]
    public function createFromDto(): void
    {
        $credentialStatusCases = CredentialStatus::cases();
        $expectedCredentialStatus = $credentialStatusCases[array_rand($credentialStatusCases)];

        $dtoStub = $this->createConfiguredStub(UserCredentialDtoInterface::class, [
            'getUserId' => $expectedUserId = uniqid(),
            'isActive' => $expectedActive = (bool)rand(0, 1),
            'getCreatedAt' => $expectedCreatedAt = date('Y-m-d H:i:s', rand()),
            'getUserUpdatedAt' => $expectedUserUpdatedAt = date('Y-m-d H:i:s', rand()),
            'getLastOrderAt' => $expectedLastOrderAt = date('Y-m-d H:i:s', rand()),
            'getCredentialStatus' => $expectedCredentialStatus,
            'getCredentialHashScheme' => $expectedCredentialHashScheme = uniqid(),
        ]);

        $sut = $this->getSut();
        $result = $sut->createFromDto($dtoStub);

        $this->assertSame($expectedUserId, $result['user_id']);
        $this->assertSame($expectedActive, $result['active']);
        $this->assertSame($expectedCreatedAt, $result['created_at']);
        $this->assertSame($expectedUserUpdatedAt, $result['user_updated_at']);
        $this->assertSame($expectedLastOrderAt, $result['last_order_at']);
        $this->assertSame($expectedCredentialStatus->value, $result['credential_status']);
        $this->assertSame($expectedCredentialHashScheme, $result['credential_hash_scheme']);
    }

    #[Test]
    public function createFromDtoThrowsExceptionForInvalidDtoType(): void
    {
        $invalidDto = $this->createStub(ExportableDtoInterface::class);

        $sut = $this->getSut();

        $this->expectException(InvalidDtoTypeException::class);
        $this->expectExceptionMessage(
            (new InvalidDtoTypeException($invalidDto, UserCredentialDtoInterface::class))->getMessage()
        );

        $sut->createFromDto($invalidDto);
    }

    private function getSut(): ArrayFactoryInterface
    {
        return new UserCredentialArrayFactory();
    }
}
