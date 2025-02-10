<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Factory;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBarFactory implements ProgressBarFactoryInterface
{
    public function create(OutputInterface $output, int $maxSteps): ProgressBar
    {
        $progressBar = new ProgressBar($output, $maxSteps);
        $progressBar->setFormat('%current%/%max% [%bar%] %percent:3s%%');
        return $progressBar;
    }
}
