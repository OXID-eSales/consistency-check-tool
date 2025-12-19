<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

use OxidEsales\ConsistencyCheck\Shared\Service\MessageFormatterServiceInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerboseConsoleLogReporter implements PostCommandLoggerInterface
{
    private const MESSAGE_LOG_OUTPUT_START = '--- Log Output ---';
    private const MESSAGE_LOG_OUTPUT_END = '--- End of Log ---';
    private const MESSAGE_LOG_OUTPUT_EMPTY = '(Log file is empty or missing)';

    public function __construct(
        private readonly LogReaderInterface $logReader,
        private readonly MessageFormatterServiceInterface $formatter
    ) {
    }

    public function after(OutputInterface $output): void
    {
        if ($output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return;
        }

        $output->writeln("\n" . $this->formatter->formatInfo(self::MESSAGE_LOG_OUTPUT_START));

        $lines = $this->logReader->readLines();

        if (empty($lines)) {
            $output->writeln(
                "\n" . $this->formatter->formatComment(self::MESSAGE_LOG_OUTPUT_EMPTY)
            );
            return;
        }

        foreach ($lines as $line) {
            $output->writeln($line);
        }

        $output->writeln("\n" . $this->formatter->formatInfo(self::MESSAGE_LOG_OUTPUT_END));
    }
}
