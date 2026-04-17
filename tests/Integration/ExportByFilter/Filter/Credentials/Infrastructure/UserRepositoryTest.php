<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\ExportByFilter\Filter\Credentials\Infrastructure;

use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Dto\UserCredentialDtoInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory\UserCredentialDtoFactoryInterface;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Infrastructure\UserRepository;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Infrastructure\UserRepositoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UserRepository::class)]
final class UserRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function findUsersWithOutdatedCredentials(): void
    {
        $sha512UserId = $this->insertUser(hash('sha512', uniqid()));
        $md5UserId = $this->insertUser(md5(uniqid()));
        $this->insertUser('$2y$10$' . str_repeat('a', 53));
        $this->insertUser('');

        $result = $this->getSut()->findUsersWithOutdatedCredentials();

        $userIds = array_map(fn($dto) => $dto->getUserId(), $result);
        $this->assertContains($sha512UserId, $userIds);
        $this->assertContains($md5UserId, $userIds);
    }

    private function insertUser(string $password): string
    {
        $userId = uniqid('user_');
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder
            ->insert('oxuser')
            ->values([
                'OXID' => ':oxid',
                'OXUSERNAME' => ':username',
                'OXPASSWORD' => ':password',
                'OXACTIVE' => '1',
            ])
            ->setParameters([
                'oxid' => $userId,
                'username' => $userId . '@test.com',
                'password' => $password,
            ])
            ->execute();

        return $userId;
    }

    private function createDtoFactoryMock(): UserCredentialDtoFactoryInterface
    {
        $dtoFactory = $this->createMock(UserCredentialDtoFactoryInterface::class);
        $dtoFactory->method('createFromArray')->willReturnCallback(function (array $data) {
            $dto = $this->createStub(UserCredentialDtoInterface::class);
            $dto->method('getUserId')->willReturn($data['OXID']);
            return $dto;
        });

        return $dtoFactory;
    }

    private function getSut(): UserRepositoryInterface
    {
        return new UserRepository(
            queryBuilderFactory: $this->get(QueryBuilderFactoryInterface::class),
            userCredentialDtoFactory: $this->createDtoFactoryMock(),
        );
    }
}
