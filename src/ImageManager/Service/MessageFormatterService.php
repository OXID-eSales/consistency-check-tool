<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ImageManager\Service;

class MessageFormatterService implements MessageFormatterServiceInterface
{
    public function formatInfo(string $message, ...$args): string
    {
        return sprintf('<info>' . $message . '</info>', ...$args);
    }

    public function formatError(string $message, ...$args): string
    {
        return sprintf('<error>' . $message . '</error>', ...$args);
    }

    public function formatComment(string $message, ...$args): string
    {
        return sprintf('<comment>' . $message . '</comment>', ...$args);
    }
}
