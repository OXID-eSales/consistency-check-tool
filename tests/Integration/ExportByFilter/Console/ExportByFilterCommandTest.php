<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\ExportByFilter\Console;

use Doctrine\DBAL\Connection;
use OxidEsales\ConsistencyCheck\ExportByFilter\Console\ExportByFilterCommand;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ExportByFilterCommandTest extends IntegrationTestCase
{
    private CommandTester $commandTester;
    private array $insertedUserIds = [];

    public function setUp(): void
    {
        parent::setUp();

        $command = $this->get(ExportByFilterCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    #[Test]
    public function executeWithDeprecatedCredentialsFilter(): void
    {
        $userId = $this->insertUserWithMd5Password();

        $exitCode = $this->commandTester->execute(['--filter-name' => 'deprecated-credentials']);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Exported', $output);
        $this->assertStringContainsString($userId, $output);
    }

    private function insertUserWithMd5Password(): string
    {
        $userId = 'test_user_' . uniqid();
        $this->insertedUserIds[] = $userId;

        $md5Password = md5('testpassword');

        $qb = $this->get(QueryBuilderFactoryInterface::class)->create();
        $qb->insert('oxuser')
            ->values([
                'OXID' => ':oxid',
                'OXACTIVE' => ':active',
                'OXRIGHTS' => ':rights',
                'OXSHOPID' => ':shopId',
                'OXUSERNAME' => ':username',
                'OXPASSWORD' => ':password',
                'OXCREATE' => ':created',
            ])
            ->setParameters([
                'oxid' => $userId,
                'active' => 1,
                'rights' => 'user',
                'shopId' => 1,
                'username' => $userId . '@test.com',
                'password' => $md5Password,
                'created' => date('Y-m-d H:i:s'),
            ])
            ->executeStatement();

        return $userId;
    }

    public function tearDown(): void
    {
        if (!empty($this->insertedUserIds)) {
            $qb = $this->get(QueryBuilderFactoryInterface::class)->create();
            $qb->delete('oxuser')
                ->where($qb->expr()->in('OXID', ':ids'))
                ->setParameter('ids', $this->insertedUserIds, Connection::PARAM_STR_ARRAY)
                ->executeStatement();
        }

        parent::tearDown();
    }
}
