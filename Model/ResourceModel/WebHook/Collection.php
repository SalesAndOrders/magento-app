<?php

namespace SalesAndOrders\FeedTool\Model\ResourceModel\WebHook;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Comment is required here
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'perspective_webhooks_collection';
    protected $_eventObject = 'perspective_webhooks_collection';
    protected $_mainTable = 'perspective_webhooks';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \SalesAndOrders\FeedTool\Model\WebHook::class,
            \SalesAndOrders\FeedTool\Model\ResourceModel\WebHook::class
        );
    }
}
