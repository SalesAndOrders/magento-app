<?php

namespace SalesAndOrders\FeedTool\Model;

use \Magento\Framework\Model\AbstractModel;
use \Magento\Framework\DataObject\IdentityInterface;

class Product extends AbstractModel implements IdentityInterface{

    const CACHE_TAG = 'perspective_products';

    protected $_cacheTag = 'perspective_products';

    protected $_eventPrefix = 'perspective_products';

    protected function _construct()
    {
        $this->_init('SalesAndOrders\FeedTool\Model\ResourceModel\Product');
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