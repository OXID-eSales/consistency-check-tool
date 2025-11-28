<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Integration\SeoUrl\Repository;

use OxidEsales\ConsistencyCheck\Export\Factory\DtoFactoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoTypeTableMapping;
use OxidEsales\ConsistencyCheck\SeoUrl\Repository\SeoUrlRepository;
use OxidEsales\ConsistencyCheck\SeoUrl\Repository\SeoUrlRepositoryInterface;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Test;

final class SeoUrlRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function findUnusedUrls(): void
    {
        $orphanedObjectId = uniqid();
        $orphanedSeoUrl = uniqid() . '.html';
        $this->insertSeoUrl(
            objectId: $orphanedObjectId,
            seoUrl: $orphanedSeoUrl,
            type: 'oxarticle'
        );

        $existingObjectId = uniqid();
        $this->insertArticle($existingObjectId);
        $this->insertSeoUrl(
            objectId: $existingObjectId,
            seoUrl: uniqid() . '.html',
            type: 'oxarticle'
        );

        $mapping = new SeoTypeTableMapping('oxarticle', 'oxarticles');
        $result = $this->getSut()->findUnusedUrls($mapping);

        $this->assertCount(1, $result);
        $this->assertSame($orphanedObjectId, $result[0]->getObjectId());
        $this->assertSame($orphanedSeoUrl, $result[0]->getSeoUrl());
    }

    #[Test]
    public function findUnusedUrlsReturnsEmptyWhenAllHaveReferences(): void
    {
        $existingObjectId = uniqid();
        $this->insertArticle($existingObjectId);
        $this->insertSeoUrl(
            objectId: $existingObjectId,
            seoUrl: uniqid() . '.html',
            type: 'oxarticle'
        );

        $mapping = new SeoTypeTableMapping('oxarticle', 'oxarticles');
        $result = $this->getSut()->findUnusedUrls($mapping);

        $this->assertEmpty($result);
    }

    #[Test]
    public function findUnusedUrlsReturnsEmptyWhenTableIsEmpty(): void
    {
        $mapping = new SeoTypeTableMapping('oxarticle', 'oxarticles');
        $result = $this->getSut()->findUnusedUrls($mapping);

        $this->assertEmpty($result);
    }

    #[Test]
    public function findDuplicateUrls(): void
    {
        $suffix = uniqid();
        $urlWithSuffix = uniqid() . $suffix . '.html';

        $this->insertSeoUrl(
            objectId: uniqid(),
            seoUrl: $urlWithSuffix,
            type: 'oxarticle'
        );
        $this->insertSeoUrl(
            objectId: uniqid(),
            seoUrl: uniqid() . '.html',
            type: 'oxarticle'
        );

        $result = $this->getSut()->findDuplicateUrls($suffix);

        $this->assertCount(1, $result);
        $this->assertSame($urlWithSuffix, $result[0]->getSeoUrl());
    }

    #[Test]
    public function findDuplicateUrlsReturnsEmptyWhenNoMatches(): void
    {
        $this->insertSeoUrl(
            objectId: uniqid(),
            seoUrl: uniqid() . '.html',
            type: 'oxarticle'
        );

        $result = $this->getSut()->findDuplicateUrls(uniqid());

        $this->assertEmpty($result);
    }

    #[Test]
    public function findDuplicateUrlsReturnsEmptyWhenTableIsEmpty(): void
    {
        $result = $this->getSut()->findDuplicateUrls(uniqid());

        $this->assertEmpty($result);
    }

    #[Test]
    public function deleteUrls(): void
    {
        $oxid1 = uniqid();
        $oxid2 = uniqid();
        $oxid3 = uniqid();

        $this->insertSeoUrl(objectId: $oxid1, seoUrl: uniqid() . '.html', type: 'oxarticle');
        $this->insertSeoUrl(objectId: $oxid2, seoUrl: uniqid() . '.html', type: 'oxarticle');
        $this->insertSeoUrl(objectId: $oxid3, seoUrl: uniqid() . '.html', type: 'oxarticle');

        $deletedCount = $this->getSut()->deleteUrls([$oxid1, $oxid2]);

        $this->assertSame(2, $deletedCount);

        // Verify oxid3 still exists
        $mapping = new SeoTypeTableMapping('oxarticle', 'oxarticles');
        $remaining = $this->getSut()->findUnusedUrls($mapping);
        $this->assertCount(1, $remaining);
        $this->assertSame($oxid3, $remaining[0]->getObjectId());
    }

    #[Test]
    public function deleteUrlsWithEmptyArray(): void
    {
        $deletedCount = $this->getSut()->deleteUrls([]);

        $this->assertSame(0, $deletedCount);
    }

    private function insertSeoUrl(string $objectId, string $seoUrl, string $type): void
    {
        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();

        $queryBuilder
            ->insert('oxseo')
            ->values([
                'OXOBJECTID' => ':objectId',
                'OXIDENT' => ':ident',
                'OXSHOPID' => ':shopId',
                'OXLANG' => ':lang',
                'OXSTDURL' => ':stdUrl',
                'OXSEOURL' => ':seoUrl',
                'OXTYPE' => ':type',
                'OXFIXED' => ':fixed',
                'OXEXPIRED' => ':expired',
                'OXPARAMS' => ':params',
            ])
            ->setParameters([
                'objectId' => $objectId,
                'ident' => md5($seoUrl),
                'shopId' => 1,
                'lang' => 0,
                'stdUrl' => 'index.php?cl=details&anid=' . $objectId,
                'seoUrl' => $seoUrl,
                'type' => $type,
                'fixed' => 0,
                'expired' => 0,
                'params' => '',
            ])
            ->execute();
    }

    private function insertArticle(string $articleId): BaseModel
    {
        $article = oxNew(Article::class);
        $article->setId($articleId);
        $article->setSkipAssign(true);
        $article->assign([
            'oxparentid' => '',
            'oxartnum' => uniqid(),
            'oxtitle' => uniqid(),
            'oxshopid' => 1,
            'oxactive' => 1
        ]);
        $article->save();

        return $article;
    }

    private function getSut(): SeoUrlRepositoryInterface
    {
        return new SeoUrlRepository(
            $this->get(QueryBuilderFactoryInterface::class),
            $this->get(DtoFactoryInterface::class)
        );
    }
}
