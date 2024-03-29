<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;

/**
 * Class Webhook Model
 */
class WebHook extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'perspective_webhooks';

    protected $_cacheTag = 'perspective_webhooks';

    protected $_eventPrefix = 'perspective_webhooks';

    protected function _construct()
    {
        $this->_init(\SalesAndOrders\FeedTool\Model\ResourceModel\WebHook::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
