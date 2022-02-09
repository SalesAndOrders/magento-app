<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Plugin\Store\ConfigManager;

use Magento\Framework\Module\ResourceInterface;
use mysql_xdevapi\Result;

/**
 * Comment is required here
 */
class GetStoreConfigs
{
    public function afterGetStoreConfigs($subject, $result, $storeCodes)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $version = $objectManager->get(ResourceInterface::class)->getDbVersion('SalesAndOrders_FeedTool');
        if (is_array($result)) {
            foreach ($result as $storeConfig) {
                $storeConfig->getExtensionAttributes()->setSalesAndOrdersFeedToolVersion($version);
            }
        }
        return $result;
    }
}
