<?php

namespace SalesAndOrders\FeedTool\Model\ResourceModel\Mapper;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'perspective_attribute_mapper_collection';
    protected $_eventObject = 'perspective_mapper_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('SalesAndOrders\FeedTool\Model\Mapper', 'SalesAndOrders\FeedTool\Model\ResourceModel\Mapper');
    }
}