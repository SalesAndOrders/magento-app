<?php

namespace SalesAndOrders\FeedTool\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use \Magento\Framework\Model\ResourceModel\Db\Context;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Mapper extends AbstractDb {

    /**
     * Mapper constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }


    protected function _construct()
    {
        $this->_init('perspective_attribute_mapper', 'id');
    }

    public function getMapperData()
    {

    }
}