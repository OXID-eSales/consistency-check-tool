<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Unit\ImageManager\Console;

use OxidEsales\ConsistencyCheck\ImageManager\Console\DeleteUnusedImagesCommand;
use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageCheckerServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageEntityFilterServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageManagerServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\MessageFormatterServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteUnusedImagesCommandTest extends TestCase
{
    #[Test]
    public function itDeletesUnusedImagesSuccessfully(): void
    {
        $entityStub = $this->createEntityStub($entityNameToFilter = uniqid());
        $unusedImages = $this->createStub(ImageCollectionInterface::class);
        $unusedImages->method('getAll')->willReturn([$this->createStub(ImageEntityInterface::class)]);

        $imageCheckerServiceStub = $this->createStub(ImageCheckerServiceInterface::class);
        $imageCheckerServiceStub
            ->method('getUnusedImages')
            ->willReturn($unusedImages);

        $imageManagerServiceStub = $this->createStub(ImageManagerServiceInterface::class);
        $imageManagerServiceStub
            ->expects(self::once())
            ->method('deleteImages');

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
            messageFormatter: $formatterMock
        );

        $tester = new CommandTester($sut);
        $tester->execute(['--type' => $entityNameToFilter]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Processing unused images for entity', $output);
        $this->assertStringContainsString('Deleted 1 images for', $output);
        $this->assertStringContainsString('Unused image deletion operation completed.', $output);
    }

    #[Test]
    public function itOutputsMessageWhenNoUnusedImagesFound(): void
    {
        $entityStub = $this->createEntityStub($entityNameToFilter = uniqid());
        $unusedImages = $this->createStub(ImageCollectionInterface::class);
        $unusedImages->method('getAll')->willReturn([]);

        $imageCheckerServiceStub = $this->createStub(ImageCheckerServiceInterface::class);
        $imageCheckerServiceStub->method('getUnusedImages')->willReturn($unusedImages);

        $imageManagerServiceStub = $this->createStub(ImageManagerServiceInterface::class);
        $imageManagerServiceStub
            ->expects(self::never())
            ->method('deleteImages');

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
        $tester->execute(['--type' => $entityNameToFilter]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('No unused images found for entity', $output);
    }

    #[Test]
    public function itLogsErrorWhenExceptionOccurs(): void
    {
        $entityMock = $this->createEntityStub($entityNameToFilter = uniqid());

        $imageCheckerServiceStub = $this->createStub(ImageCheckerServiceInterface::class);
        $imageCheckerServiceStub
            ->method('getUnusedImages')
            ->willThrowException(new \RuntimeException());

        $entityFilterServiceSpy = $this->createStub(ImageEntityFilterServiceInterface::class);
        $entityFilterServiceSpy
            ->method('filterEntitiesByName')
            ->willReturn([$entityMock]);

        $loggerMock = $this->createMock(PsrLoggerInterface::class);
        $loggerMock
            ->method('error')
            ->with($this->stringContains($exceptionMessage = 'Error processing entity'));

        $formatterMock = $this->createMock(MessageFormatterServiceInterface::class);
        $formatterMock
            ->method('formatError')
            ->willReturnCallback(function ($message, ...$args) {
                return sprintf('<error>' . $message . '</error>', ...$args);
            });

        $sut = $this->getSut(
            entities: [$entityMock],
            imageCheckerService: $imageCheckerServiceStub,
            imageEntityFilter: $entityFilterServiceSpy,
            messageFormatter: $formatterMock,
            logger: $loggerMock
        );

        $commandTester = new CommandTester($sut);
        $commandTester->execute(['--type' => $entityNameToFilter]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString($exceptionMessage, $output);
    }


    private function createEntityStub(string $name = null): ImageEntityInterface
    {
        $entityStub = $this->createStub(ImageEntityInterface::class);
        $entityStub->method('getName')->willReturn($name ?? uniqid());
        $entityStub->method('getFieldName')->willReturn(uniqid());
        return $entityStub;
    }

    private function createProgressBarStub(): ProgressBar
    {
        return  new ProgressBar(new NullOutput());
    }

    private function getSut(
        array $entities = [],
        ?ImageCheckerServiceInterface $imageCheckerService = null,
        ?ImageManagerServiceInterface $imageManagerService = null,
        ?ImageEntityFilterServiceInterface $imageEntityFilter = null,
        ?MessageFormatterServiceInterface $messageFormatter = null,
        ?LoggerInterface $logger = null,
    ): DeleteUnusedImagesCommand {
        $imageCheckerService ??= $this->createStub(ImageCheckerServiceInterface::class);
        $imageManagerService ??= $this->createStub(ImageManagerServiceInterface::class);
        $imageEntityFilter ??= $this->createStub(ImageEntityFilterServiceInterface::class);
        $messageFormatter ??= $this->createStub(MessageFormatterServiceInterface::class);
        $progressBar = $this->createProgressBarStub();
        $logger ??= $this->createStub(LoggerInterface::class);

        return new DeleteUnusedImagesCommand(
            entities: $entities,
            imageCheckerService: $imageCheckerService,
            imageManagerService: $imageManagerService,
            entityFilterService: $imageEntityFilter,
            messageFormatter: $messageFormatter,
            progressBar: $progressBar,
            logger: $logger
        );
    }
}
