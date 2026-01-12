<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

require_once '/var/www/source/bootstrap.php';

use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Facts\Facts;

if ((new Facts())->getEdition() !== 'CE') {
    $shop = oxNew(Shop::class);

    if (!$shop->load(2)) {
        $shop->setId(2);
        $shop->assign([
            'oxactive' => 1,
            'oxname' => 'Test Shop 2',
        ]);
        $shop->save();

        // Copy language config from Shop 1 to Shop 2 (required for view generation)
        $config = Registry::getConfig();
        foreach (['aLanguageParams', 'aLanguages'] as $configName) {
            $value = $config->getShopConfVar($configName, 1);
            if ($value !== null) {
                $config->saveShopConfVar('aarr', $configName, $value, 2);
            }
        }
    }

    $shop->generateViews();
}
