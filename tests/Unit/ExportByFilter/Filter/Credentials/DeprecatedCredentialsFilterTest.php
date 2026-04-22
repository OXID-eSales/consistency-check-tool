<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\ExportByFilter\Filter\Credentials;

use OxidEsales\ConsistencyCheck\Export\Factory\ArrayFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\DeprecatedCredentialsFilter;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDtoInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Infrastructure\UserRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeprecatedCredentialsFilter::class)]
final class DeprecatedCredentialsFilterTest extends TestCase
{
    #[Test]
    public function getName(): void
    {
        $this->assertSame('deprecated-credentials', $this->getSut()->getName());
    }

    #[Test]
    public function getHeaders(): void
    {
        $this->assertSame([
            'user_id',
            'active',
            'created_at',
            'user_updated_at',
            'last_order_at',
            'credential_status',
            'credential_hash_scheme',
        ], $this->getSut()->getHeaders());
    }

    #[Test]
    public function getArrayFactory(): void
    {
        $arrayFactoryStub = $this->createStub(ArrayFactoryInterface::class);

        $this->assertSame($arrayFactoryStub, $this->getSut(arrayFactory: $arrayFactoryStub)->getArrayFactory());
    }

    #[Test]
    public function getItems(): void
    {
        $dtoStub = $this->createStub(UserCredentialDtoInterface::class);
        $userRepositoryStub = $this->createStub(UserRepositoryInterface::class);
        $userRepositoryStub->method('findUsersWithOutdatedCredentials')->willReturn([$dtoStub]);

        $this->assertSame([$dtoStub], $this->getSut(userRepository: $userRepositoryStub)->getItems());
    }

    private function getSut(
        ?UserRepositoryInterface $userRepository = null,
        ?ArrayFactoryInterface $arrayFactory = null,
    ): DeprecatedCredentialsFilter {
        return new DeprecatedCredentialsFilter(
            $userRepository ?? $this->createStub(UserRepositoryInterface::class),
            $arrayFactory ?? $this->createStub(ArrayFactoryInterface::class),
        );
    }
}
