<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\SeoUrl\Service;

use OxidEsales\ConsistencyCheck\SeoUrl\Dto\SeoUrlDtoInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Exception\MissingSuffixException;
use OxidEsales\ConsistencyCheck\SeoUrl\Infrastructure\SeoUrlRepositoryInterface;
use OxidEsales\ConsistencyCheck\SeoUrl\Service\SeoUrlService;
use OxidEsales\ConsistencyCheck\SeoUrl\Service\SeoUrlServiceInterface;
use OxidEsales\Eshop\Core\Config;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SeoUrlServiceTest extends TestCase
{
    #[Test]
    public function findUnusedUrls(): void
    {
        $dtoStub = $this->createStub(SeoUrlDtoInterface::class);

        $repositoryStub = $this->createStub(SeoUrlRepositoryInterface::class);
        $repositoryStub->method('findUnusedUrls')->willReturn([$dtoStub]);

        $sut = $this->getSut(repositoryStub: $repositoryStub);
        $result = $sut->findUnusedUrls();

        $this->assertCount(1, $result);
        $this->assertSame($dtoStub, $result[0]);
    }

    #[Test]
    public function findDuplicateUrlsUsesShopConfig(): void
    {
        $dtoStub = $this->createStub(SeoUrlDtoInterface::class);
        $suffix = uniqid();

        $repositoryMock = $this->createMock(SeoUrlRepositoryInterface::class);
        $repositoryMock->method('findDuplicateUrls')->with($suffix)->willReturn([$dtoStub]);

        $configStub = $this->createConfiguredStub(Config::class, [
            'getConfigParam' => $suffix,
        ]);

        $sut = $this->getSut(repositoryStub: $repositoryMock, configStub: $configStub);
        $result = $sut->findDuplicateUrls(null);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function findDuplicateUrlsUsesProvidedSuffix(): void
    {
        $dtoStub = $this->createStub(SeoUrlDtoInterface::class);
        $suffix = uniqid();

        $repositoryMock = $this->createMock(SeoUrlRepositoryInterface::class);
        $repositoryMock->method('findDuplicateUrls')->with($suffix)->willReturn([$dtoStub]);

        $sut = $this->getSut(repositoryStub: $repositoryMock);
        $result = $sut->findDuplicateUrls($suffix);

        $this->assertCount(1, $result);
    }

    #[Test]
    public function findDuplicateUrlsThrowsExceptionWhenSuffixIsNullAndConfigNotSet(): void
    {
        $configStub = $this->createConfiguredStub(Config::class, [
            'getConfigParam' => null,
        ]);

        $sut = $this->getSut(configStub: $configStub);

        $this->expectException(MissingSuffixException::class);

        $sut->findDuplicateUrls(null);
    }

    #[Test]
    public function findDuplicateUrlsThrowsExceptionWhenSuffixIsEmptyString(): void
    {
        $sut = $this->getSut();

        $this->expectException(MissingSuffixException::class);

        $sut->findDuplicateUrls('');
    }

    #[Test]
    public function deleteUrls(): void
    {
        $oxids = [uniqid(), uniqid(), uniqid()];
        $deletedCount = rand(1, 100);

        $repositoryMock = $this->createMock(SeoUrlRepositoryInterface::class);
        $repositoryMock->method('deleteUrls')->with($oxids)->willReturn($deletedCount);

        $sut = $this->getSut(repositoryStub: $repositoryMock);
        $result = $sut->deleteUrls($oxids);

        $this->assertSame($deletedCount, $result);
    }

    private function getSut(
        ?SeoUrlRepositoryInterface $repositoryStub = null,
        ?Config $configStub = null
    ): SeoUrlServiceInterface {
        $repositoryStub ??= $this->createStub(SeoUrlRepositoryInterface::class);
        $configStub ??= $this->createStub(Config::class);

        return new SeoUrlService($repositoryStub, $configStub);
    }
}
