<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use Symfony\Component\Console\Output\OutputInterface;

interface PostCommandLoggerInterface
{
    public function after(OutputInterface $output): void;
}
