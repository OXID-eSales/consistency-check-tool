<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ForwardCompatibility\Result;
use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Factory\SeoUrlDtoFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

/**
 * @phpstan-import-type SeoUrlTableRow from SeoUrlDtoFactoryInterface
 */
final class SeoUrlRepository implements SeoUrlRepositoryInterface
{
    private const ROOT_OBJECT_ID = 'root';

    /**
     * @param iterable<SeoTypeTableMappingInterface> $seoTypeTableMappings
     */
    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
        private readonly SeoUrlDtoFactoryInterface $seoUrlDtoFactory,
        private readonly iterable $seoTypeTableMappings,
    ) {
    }

    public function findUnusedUrls(): array
    {
        $allDtos = [];
        foreach ($this->seoTypeTableMappings as $mapping) {
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
            ->andWhere('s.OXOBJECTID != :rootId')
            ->setParameter('type', $mapping->getSeoType())
            ->setParameter('rootId', self::ROOT_OBJECT_ID);

        /** @var Result<array> $result */
        $result = $queryBuilder->execute();

        $dtos = [];
        /** @var SeoUrlTableRow $row */
        while ($row = $result->fetchAssociative()) {
            $dtos[] = $this->seoUrlDtoFactory->createFromArray($row);
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

        /** @var Result<array> $result */
        $result = $queryBuilder->execute();

        $dtos = [];
        /** @var SeoUrlTableRow $row */
        while ($row = $result->fetchAssociative()) {
            $dtos[] = $this->seoUrlDtoFactory->createFromArray($row);
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
