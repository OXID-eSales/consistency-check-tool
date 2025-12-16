<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ForwardCompatibility\Result;
use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

final class SeoUrlRepository implements SeoUrlRepositoryInterface
{
    /**
     * @param iterable<SeoTypeTableMappingInterface> $mappings
     */
    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
        private readonly DtoFactoryInterface $factory,
        private readonly iterable $mappings,
    ) {
    }

    public function findUnusedUrls(): array
    {
        $allDtos = [];
        foreach ($this->mappings as $mapping) {
            $dtos = $this->findUnusedUrlsForMapping($mapping);
            $allDtos = array_merge($allDtos, $dtos);
        }
        return $allDtos;
    }

    /**
     * @return array<SeoUrlDtoInterface>
     */
    private function findUnusedUrlsForMapping(SeoTypeTableMappingInterface $mapping): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        $queryBuilder
            ->select('s.*')
            ->from('oxseo', 's')
            ->leftJoin('s', $mapping->getReferenceTable(), 'r', 's.OXOBJECTID = r.OXID')
            ->where('s.OXTYPE = :type')
            ->andWhere('r.OXID IS NULL')
            ->setParameter('type', $mapping->getSeoType());

        /** @var Result $result */
        $result = $queryBuilder->execute(); /** @phpstan-ignore missingType.iterableValue */
        $rows = $result->fetchAllAssociative();

        $dtos = [];
        foreach ($rows as $row) {
            $dto = $this->factory->createFromArray($row);
            assert($dto instanceof SeoUrlDtoInterface);
            $dtos[] = $dto;
        }

        return $dtos;
    }

    public function findDuplicateUrls(string $suffix): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        $queryBuilder
            ->select('*')
            ->from('oxseo')
            ->where('OXSEOURL LIKE :pattern')
            ->setParameter('pattern', '%' . $suffix . '%');

        /** @var Result $result */
        $result = $queryBuilder->execute(); /** @phpstan-ignore missingType.iterableValue */
        $rows = $result->fetchAllAssociative();

        $dtos = [];
        foreach ($rows as $row) {
            $dto = $this->factory->createFromArray($row);
            assert($dto instanceof SeoUrlDtoInterface);
            $dtos[] = $dto;
        }

        return $dtos;
    }

    public function deleteUrls(array $oxids): int
    {
        if (empty($oxids)) {
            return 0;
        }

        $queryBuilder = $this->queryBuilderFactory->create();

        $result = $queryBuilder
            ->delete('oxseo')
            ->where('OXOBJECTID IN (:oxids)')
            ->setParameter('oxids', $oxids, Connection::PARAM_STR_ARRAY)
            ->execute();

        return is_int($result) ? $result : 0;
    }
}
