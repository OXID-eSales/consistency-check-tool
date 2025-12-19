<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Console;

use OxidEsales\ConsistencyCheck\ImageManager\Dto\ImageCollectionInterface;
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

    protected function processImages(ImageCollectionInterface $unusedImages, InputInterface $input): int
    {
        return $this->imageManagerService->moveImages(
            $unusedImages,
            $input->getOption('destination'),
            $input->getOption('dry-run')
        );
    }

    protected function getMessageProcessedImages(): string
    {
        return self::MESSAGE_MOVED_IMAGES;
    }

    protected function getMessageCompletion(): string
    {
        return self::MESSAGE_COMPLETION;
    }
}
