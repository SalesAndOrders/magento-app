<?php

namespace SalesAndOrders\FeedTool\Model\ResourceModel\Product;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'perspective_products_collection';
    protected $_eventObject = 'products_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('SalesAndOrders\FeedTool\Model\Product', 'SalesAndOrders\FeedTool\Model\ResourceModel\Product');
    }



}