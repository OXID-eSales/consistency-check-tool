<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Infrastructure;

use Doctrine\DBAL\ForwardCompatibility\Result;
use OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Factory\UserCredentialDtoFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

/**
 * @phpstan-import-type UserTableRow from UserCredentialDtoFactoryInterface
 */
final class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly QueryBuilderFactoryInterface $queryBuilderFactory,
        private readonly UserCredentialDtoFactoryInterface $userCredentialDtoFactory,
    ) {
    }

    public function findUsersWithOutdatedCredentials(): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();

        $queryBuilder
            ->select(
                'u.OXID',
                'u.OXACTIVE',
                'u.OXCREATE',
                'u.OXTIMESTAMP',
                'u.OXPASSWORD',
                "COALESCE(MAX(o.OXORDERDATE), '') as OXLASTORDER"
            )
            ->from('oxuser', 'u')
            ->leftJoin('u', 'oxorder', 'o', 'o.OXUSERID = u.OXID')
            ->where('u.OXPASSWORD NOT LIKE :bcryptPattern')
            ->andWhere('u.OXPASSWORD != :emptyPassword')
            ->groupBy('u.OXID')
            ->setParameter('bcryptPattern', '$2y$%')
            ->setParameter('emptyPassword', '');

        /** @var Result<array> $result */
        $result = $queryBuilder->execute();

        $dtos = [];
        while ($row = $result->fetchAssociative()) {
            /** @var UserTableRow $row */
            $dtos[] = $this->userCredentialDtoFactory->createFromArray($row);
        }

        return $dtos;
    }
}
