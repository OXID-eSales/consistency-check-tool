<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class MoveUnusedImagesCommand extends AbstractUnusedImagesCommand
{
    protected static $defaultName = 'oe:consistency_check:move-unused-images';

    protected const MESSAGE_MOVED_IMAGES = 'Moved %d images for entity %s';
    protected const MESSAGE_COMPLETION = 'Unused image move operation completed.';
    protected const ERROR_DESTINATION_REQUIRED = 'Error: The --destination option is required.';

    private const COMMAND_DESCRIPTION = 'Moves unused images to a new destination.';
    private const COMMAND_OPTION_TYPE = 'Entity type to process (product, category, manufacturer)';
    private const COMMAND_OPTION_DESTINATION = 'Destination path (required)';
    private const COMMAND_OPTION_DRY_RUN = 'Simulate the move operation without making changes';

    protected function configure(): void
    {
        $this
            ->setDescription(self::COMMAND_DESCRIPTION)
			// phpcs:ignore Generic.Files.LineLength.TooLong
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, self::COMMAND_OPTION_TYPE)
            ->addOption('destination', null, InputOption::VALUE_REQUIRED, self::COMMAND_OPTION_DESTINATION)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, self::COMMAND_OPTION_DRY_RUN);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $destination = $input->getOption('destination');
        if (!$destination) {
            $output->writeln($this->messageFormatter->formatError(self::ERROR_DESTINATION_REQUIRED));
            return Command::INVALID;
        }

        return parent::execute($input, $output);
    }

    protected function processEntity($entity, InputInterface $input, OutputInterface $output): void
    {
        $unusedImages = $this->imageCheckerService->getUnusedImages($entity);
        $entityDetails = sprintf('[%s:%s]', $entity->getName(), $entity->getFieldName());

        if ($unusedImages->getAll()) {
            $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_PROCESSING, $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_PROCESSING, $entityDetails));

			// phpcs:ignore Generic.Files.LineLength.TooLong
            $movedImagesCount = $this->imageManagerService->moveImages($unusedImages, $input->getOption('destination'), $input->getOption('dry-run'));

			// phpcs:ignore Generic.Files.LineLength.TooLong
            $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_MOVED_IMAGES, $movedImagesCount, $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_MOVED_IMAGES, $movedImagesCount, $entityDetails));
        } else {
            $output->writeln($this->messageFormatter->formatComment(self::MESSAGE_NO_IMAGES, $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_NO_IMAGES, $entityDetails));
        }
    }
}
