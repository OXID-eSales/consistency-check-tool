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
        $bcryptUserId = $this->insertUser('$2y$10$' . str_repeat('a', 53));
        $emptyPasswordUserId = $this->insertUser('');

        $result = $this->getSut()->findUsersWithOutdatedCredentials();

        $userIds = array_map(fn($dto) => $dto->getUserId(), $result);
        $this->assertContains($sha512UserId, $userIds);
        $this->assertContains($md5UserId, $userIds);
        $this->assertNotContains($bcryptUserId, $userIds);
        $this->assertNotContains($emptyPasswordUserId, $userIds);
    }

    #[Test]
    public function lastOrderDateIsTheLatestOne(): void
    {
        $userId = $this->insertUser(hash('sha512', uniqid()));
        $this->insertOrder($userId, $date1 = date('Y-m-d H:i:s', rand(0, time())));
        $this->insertOrder($userId, $date2 = date('Y-m-d H:i:s', rand(0, time())));
        $this->insertOrder($userId, $date3 = date('Y-m-d H:i:s', rand(0, time())));
        $expectedLastOrderDate = max($date1, $date2, $date3);

        $result = $this->getSut()->findUsersWithOutdatedCredentials();

        $dto = $this->pickDtoFromResultList($result, $userId);
        $this->assertSame($expectedLastOrderDate, $dto->getLastOrderAt());
    }

    private function pickDtoFromResultList(array $dtos, string $userId): UserCredentialDtoInterface
    {
        foreach ($dtos as $dto) {
            if ($dto->getUserId() === $userId) {
                return $dto;
            }
        }
        $this->fail("No DTO found for user $userId");
    }

    private function insertOrder(string $userId, string $orderDate): void
    {
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder
            ->insert('oxorder')
            ->values([
                'OXID' => ':oxid',
                'OXUSERID' => ':userId',
                'OXORDERDATE' => ':orderDate',
            ])
            ->setParameters([
                'oxid' => uniqid('order_'),
                'userId' => $userId,
                'orderDate' => $orderDate,
            ])
            ->execute();
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

    private function getSut(): UserRepositoryInterface
    {
        return new UserRepository(
            queryBuilderFactory: $this->get(QueryBuilderFactoryInterface::class),
            userCredentialDtoFactory: $this->get(UserCredentialDtoFactoryInterface::class),
        );
    }
}
