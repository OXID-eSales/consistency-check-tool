<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\ConsistencyCheck\ExportByFilter\Filter\Credentials\Enum;

enum CredentialStatus: string
{
    case SUPPORTED = 'supported';
    case DEPRECATED = 'deprecated';
    case UNSUPPORTED = 'unsupported';
    case NOT_SET = 'not_set';
    case UNKNOWN = 'unknown';
}
