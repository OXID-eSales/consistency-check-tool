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

class DeleteUnusedImagesCommand extends AbstractUnusedImagesCommand
{
    protected static $defaultName = 'oe:consistency_check:delete-unused-images';

    protected const MESSAGE_DELETED_IMAGES = 'Deleted %d images for entity %s';
    protected const MESSAGE_COMPLETION = 'Unused image deletion operation completed.';

    private const COMMAND_DESCRIPTION = 'Deletes unused images.';
    private const COMMAND_OPTION_TYPE = 'Entity type to process (product, category, manufacturer)';
    private const COMMAND_OPTION_DRY_RUN = 'Perform a dry run without actual file deletions';

    protected function configure(): void
    {
        $this
            ->setDescription(self::COMMAND_DESCRIPTION)
			// phpcs:ignore Generic.Files.LineLength.TooLong
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, self::COMMAND_OPTION_TYPE)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, self::COMMAND_OPTION_DRY_RUN);
    }

    protected function processEntity($entity, InputInterface $input, OutputInterface $output): void
    {
        $unusedImages = $this->imageCheckerService->getUnusedImages($entity);
        $entityDetails = sprintf('[%s:%s]', $entity->getName(), $entity->getFieldName());

        if ($unusedImages->getAll()) {
            $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_PROCESSING, $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_PROCESSING, $entityDetails));

            $this->imageManagerService->deleteImages($unusedImages, $input->getOption('dry-run'));

			// phpcs:ignore Generic.Files.LineLength.TooLong
            $output->writeln($this->messageFormatter->formatInfo(self::MESSAGE_DELETED_IMAGES, count($unusedImages->getAll()), $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_DELETED_IMAGES, count($unusedImages->getAll()), $entityDetails));
        } else {
            $output->writeln($this->messageFormatter->formatComment(self::MESSAGE_NO_IMAGES, $entityDetails));
            $this->logger->info(sprintf(self::MESSAGE_NO_IMAGES, $entityDetails));
        }
    }
}
