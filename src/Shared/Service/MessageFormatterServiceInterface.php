<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\Shared\Service;

interface MessageFormatterServiceInterface
{
    public function formatInfo(string $message, mixed ...$args): string;

    public function formatError(string $message, mixed ...$args): string;

    public function formatComment(string $message, mixed ...$args): string;
}
