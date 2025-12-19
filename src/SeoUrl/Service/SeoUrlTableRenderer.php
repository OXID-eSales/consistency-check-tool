<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\SeoUrl\Service;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

final class SeoUrlTableRenderer implements SeoUrlTableRendererInterface
{
    private const HEADERS = [
        'OXOBJECTID',
        'OXIDENT',
        'OXSHOPID',
        'OXLANG',
        'OXSTDURL',
        'OXSEOURL',
        'OXTYPE',
        'OXFIXED',
        'OXEXPIRED',
        'OXPARAMS',
        'OXTIMESTAMP',
    ];

    /**
     * @param array<SeoUrlDtoInterface> $seoUrls
     */
    public function render(array $seoUrls, OutputInterface $output): void
    {
        $table = new Table($output);
        $table->setHeaders(self::HEADERS);

        foreach ($seoUrls as $dto) {
            $table->addRow([
                $dto->getObjectId(),
                $dto->getIdent(),
                $dto->getShopId(),
                $dto->getLanguageId(),
                $dto->getStdUrl(),
                $dto->getSeoUrl(),
                $dto->getType(),
                $dto->getFixed(),
                $dto->getExpired(),
                $dto->getParams(),
                $dto->getTimestamp(),
            ]);
        }

        $table->render();
    }
}
