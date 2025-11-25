<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Service;

use League\Csv\Exception as CsvException;
use OxidEsales\ConsistencyCheck\Shared\Exception\InvalidFileFormatException;
use OxidEsales\ConsistencyCheck\Shared\Factory\CsvReaderFactoryInterface;
use OxidEsales\ConsistencyCheck\Shared\Mapper\CsvMapperInterface;

final class CsvReaderService implements CsvReaderServiceInterface
{
    public function __construct(
        private readonly PathResolverInterface $pathResolver,
        private readonly CsvReaderFactoryInterface $readerFactory,
    ) {
    }

    public function readFromCsv(string $filepath, CsvMapperInterface $mapper): array
    {
        $absolutePath = $this->pathResolver->getAbsolutePath($filepath);

        try {
            $csv = $this->readerFactory->create($absolutePath);
            $csv->setHeaderOffset(0);

            $dtos = [];
            foreach ($csv as $record) {
                $dtos[] = $mapper->fromArray($record);
            }

            return $dtos;
        } catch (CsvException $e) {
            throw new InvalidFileFormatException(
                "Cannot read CSV file: {$filepath}. Error: {$e->getMessage()}",
                0,
                $e
            );
        }
    }
}
