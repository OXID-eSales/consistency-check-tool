<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Tests\Integration\ImageManager\Console;

use OxidEsales\ConsistencyCheck\ImageManager\Console\DeleteUnusedImagesCommand;
use OxidEsales\ConsistencyCheck\ImageManager\Entity\ImageEntityInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Factory\ProgressBarFactoryInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageCheckerServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageEntityFilterServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\ImageManagerServiceInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\LogReaderInterface;
use OxidEsales\ConsistencyCheck\ImageManager\Service\MessageFormatterServiceInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteUnusedImagesCommandTest extends IntegrationTestCase
{
    #[Test]
    public function itSuccessfullyDeleteUnusedImages(): void
    {
        $sut = $this->getSut();

        $application = new Application();
        $application->add($sut);

        $commandTester = new CommandTester($application->find('oe:consistency_check:delete-unused-images'));
        $commandTester->execute([
            '--type' => uniqid()
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Unused image deletion operation completed.', $output);
    }

    private function getSut(): DeleteUnusedImagesCommand
    {
        return new DeleteUnusedImagesCommand(
            entities: [$this->createStub(ImageEntityInterface::class)],
            imageCheckerService: $this->get(ImageCheckerServiceInterface::class),
            imageManagerService: $this->get(ImageManagerServiceInterface::class),
            entityFilterService: $this->get(ImageEntityFilterServiceInterface::class),
            messageFormatter: $this->get(MessageFormatterServiceInterface::class),
            progressBarFactory: $this->get(ProgressBarFactoryInterface::class),
            logger: $this->get(LoggerInterface::class),
            logReader: $this->get(LogReaderInterface::class),
        );
    }
}
