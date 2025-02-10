<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Console;

use OxidEsales\ConsistencyCheck\ImageManager\Console\MoveUnusedImagesCommand;
use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ProgressBarFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageCheckerServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageEntityFilterServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageManagerServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\MessageFormatterServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;

class MoveUnusedImagesCommandTest extends TestCase
{
    #[Test]
    public function itFailsWhenDestinationIsNotProvided(): void
    {

        $formatterMock = $this->createMock(MessageFormatterServiceInterface::class);
        $formatterMock
            ->method('formatError')
            ->willReturnCallback(function ($message, ...$args) {
                return sprintf('<error>' . $message . '</error>', ...$args);
            });

        $sut = $this->getSut(messageFormatter: $formatterMock);
        $commandTester = new CommandTester($sut);
        $commandTester->execute(['--destination' => '']);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Error: The --destination option is required.', $output);
        $this->assertSame(Command::INVALID, $commandTester->getStatusCode());
    }
    #[Test]
    public function itMovesUnusedImagesSuccessfully(): void
    {
        $entityStub = $this->createEntityStub($entityNameToFilter = uniqid());
        $unusedImagesMock = $this->createMock(ImageCollectionInterface::class);
        $unusedImagesMock->method('getAll')->willReturn([$this->createMock(ImageEntityInterface::class)]);

        $imageCheckerServiceStub = $this->createStub(ImageCheckerServiceInterface::class);
        $imageCheckerServiceStub
            ->method('getUnusedImages')
            ->willReturn($unusedImagesMock);

        $imageManagerServiceStub = $this->createStub(ImageManagerServiceInterface::class);
        $imageManagerServiceStub
            ->expects(self::once())
            ->method('moveImages')
            ->willReturn(1);

        $entityFilterServiceStub = $this->createStub(ImageEntityFilterServiceInterface::class);
        $entityFilterServiceStub
            ->method('filterEntitiesByName')
            ->with([$entityStub], $entityNameToFilter)
            ->willReturn([$entityStub]);

        $formatterMock = $this->createMock(MessageFormatterServiceInterface::class);
        $formatterMock
            ->method('formatInfo')
            ->willReturnCallback(function ($message, ...$args) {
                return sprintf('<info>' . $message . '</info>', ...$args);
            });

        $sut = $this->getSut(
            entities: [$entityStub],
            imageCheckerService: $imageCheckerServiceStub,
            imageManagerService: $imageManagerServiceStub,
            imageEntityFilter: $entityFilterServiceStub,
            messageFormatter: $formatterMock,
        );

        $tester = new CommandTester($sut);
        $tester->execute(['--type' => $entityNameToFilter, '--destination' => '/backup/images']);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Processing unused images for entity', $output);
        $this->assertStringContainsString('Moved 1 images for', $output);
        $this->assertStringContainsString('Unused image move operation completed.', $output);
    }

    #[Test]
    public function itOutputsMessageWhenNoUnusedImagesFound(): void
    {
        $entityStub = $this->createEntityStub($entityNameToFilter = uniqid());
        $unusedImagesMock = $this->createMock(ImageCollectionInterface::class);
        $unusedImagesMock->method('getAll')->willReturn([]);

        $imageCheckerServiceStub = $this->createStub(ImageCheckerServiceInterface::class);
        $imageCheckerServiceStub->method('getUnusedImages')->willReturn($unusedImagesMock);

        $imageManagerServiceStub = $this->createStub(ImageManagerServiceInterface::class);
        $imageManagerServiceStub
            ->expects(self::never())
            ->method('moveImages');

        $entityFilterServiceStub = $this->createStub(ImageEntityFilterServiceInterface::class);
        $entityFilterServiceStub
            ->method('filterEntitiesByName')
            ->with([$entityStub], $entityNameToFilter)
            ->willReturn([$entityStub]);

        $formatterMock = $this->createMock(MessageFormatterServiceInterface::class);
        $formatterMock
            ->method('formatComment')
            ->willReturnCallback(function ($message, ...$args) {
                return sprintf('<comment>' . $message . '</comment>', ...$args);
            });

        $sut = $this->getSut(
            entities: [$entityStub],
            imageCheckerService: $imageCheckerServiceStub,
            imageManagerService: $imageManagerServiceStub,
            imageEntityFilter: $entityFilterServiceStub,
            messageFormatter: $formatterMock,
        );

        $tester = new CommandTester($sut);
        $tester->execute(['--type' => $entityNameToFilter, '--destination' => '/backup/images']);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('No unused images found for entity', $output);
    }

    #[Test]
    public function itLogsErrorWhenExceptionOccurs(): void
    {
        $entityStub = $this->createEntityStub($entityNameToFilter = uniqid());

        $imageCheckerServiceStub = $this->createStub(ImageCheckerServiceInterface::class);
        $imageCheckerServiceStub
            ->method('getUnusedImages')
            ->willThrowException(new \RuntimeException('Test Exception'));

        $entityFilterServiceStub = $this->createStub(ImageEntityFilterServiceInterface::class);
        $entityFilterServiceStub->method('filterEntitiesByName')->willReturn([$entityStub]);

        $formatterMock = $this->createMock(MessageFormatterServiceInterface::class);
        $formatterMock
            ->method('formatError')
            ->willReturnCallback(function ($message, ...$args) {
                return sprintf('<error>' . $message . '</error>', ...$args);
            });

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains($exceptionMessage = 'Error processing entity'));

        $sut = $this->getSut(
            entities: [$entityStub],
            imageCheckerService: $imageCheckerServiceStub,
            imageEntityFilter: $entityFilterServiceStub,
            messageFormatter: $formatterMock,
            logger: $loggerMock
        );

        $tester = new CommandTester($sut);
        $tester->execute(['--type' => $entityNameToFilter, '--destination' => '/backup/images']);

        $output = $tester->getDisplay();
        $this->assertStringContainsString($exceptionMessage, $output);
    }

    private function createEntityStub(string $name = null): ImageEntityInterface
    {
        $entityStub = $this->createStub(ImageEntityInterface::class);
        $entityStub->method('getName')->willReturn($name ?? uniqid());
        $entityStub->method('getFieldName')->willReturn(uniqid());
        return $entityStub;
    }

    private function createProgressBarFactoryStub(): ProgressBarFactoryInterface
    {
        $progressBarFactory = $this->createMock(ProgressBarFactoryInterface::class);
        $progressBarMock = new ProgressBar(new NullOutput());
        $progressBarFactory
            ->method('create')
            ->willReturn($progressBarMock);

        return $progressBarFactory;
    }

    private function getSut(
        array $entities = [],
        ?ImageCheckerServiceInterface $imageCheckerService = null,
        ?ImageManagerServiceInterface $imageManagerService = null,
        ?ImageEntityFilterServiceInterface $imageEntityFilter = null,
        ?MessageFormatterServiceInterface $messageFormatter = null,
        ?LoggerInterface $logger = null,
    ): MoveUnusedImagesCommand {
        $imageCheckerService ??= $this->createStub(ImageCheckerServiceInterface::class);
        $imageManagerService ??= $this->createStub(ImageManagerServiceInterface::class);
        $imageEntityFilter ??= $this->createStub(ImageEntityFilterServiceInterface::class);
        $messageFormatter ??= $this->createStub(MessageFormatterServiceInterface::class);
        $progressBar = $this->createProgressBarFactoryStub();
        $logger ??= $this->createStub(LoggerInterface::class);

        return new MoveUnusedImagesCommand(
            entities: $entities,
            imageCheckerService: $imageCheckerService,
            imageManagerService: $imageManagerService,
            entityFilterService: $imageEntityFilter,
            messageFormatter: $messageFormatter,
            progressBarFactory: $progressBar,
            logger: $logger
        );
    }
}
