<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Console;

use OxidEsales\ConsistencyCheck\ImageManager\DataTransferObject\ImageCollectionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

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
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, self::COMMAND_OPTION_TYPE)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, self::COMMAND_OPTION_DRY_RUN);
    }

    protected function processImages(ImageCollectionInterface $unusedImages, InputInterface $input): int
    {
        return $this->imageManagerService->deleteImages($unusedImages, $input->getOption('dry-run'));
    }

    protected function getMessageProcessedImages(): string
    {
        return self::MESSAGE_DELETED_IMAGES;
    }

    protected function getMessageCompletion(): string
    {
        return self::MESSAGE_COMPLETION;
    }
}
