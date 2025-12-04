<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Tests\Unit\Shared\Service;

use OxidEsales\ConsistencyCheck\Shared\Service\MessageFormatterService;
use OxidEsales\ConsistencyCheck\Shared\Service\MessageFormatterServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MessageFormatterServiceTest extends TestCase
{
    #[Test]
    public function itFormatsInfoMessageCorrectly(): void
    {
        $sut = $this->getSut();

        $message = uniqid();
        $arg = uniqid();
        $formattedMessage = $sut->formatInfo($message, $arg);

        $expectedOutput = sprintf('<info>' . $message . '</info>', $arg);
        $this->assertSame($expectedOutput, $formattedMessage);
    }

    #[Test]
    public function itFormatsErrorMessageCorrectly(): void
    {
        $sut = $this->getSut();

        $message = uniqid();
        $arg = uniqid();
        $formattedMessage = $sut->formatError($message, $arg);

        $expectedOutput = sprintf('<error>' . $message . '</error>', $arg);
        $this->assertSame($expectedOutput, $formattedMessage);
    }

    #[Test]
    public function itFormatsCommentMessageCorrectly(): void
    {
        $sut = $this->getSut();

        $message = uniqid();
        $arg = uniqid();
        $formattedMessage = $sut->formatComment($message, $arg);

        $expectedOutput = sprintf('<comment>' . $message . '</comment>', $arg);
        $this->assertSame($expectedOutput, $formattedMessage);
    }

    protected function getSut(): MessageFormatterServiceInterface
    {
        return new MessageFormatterService();
    }
}
