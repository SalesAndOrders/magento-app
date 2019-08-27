<?php

namespace SalesAndOrders\FeedTool\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Comment is required here
 */
class Data extends AbstractHelper
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Data constructor.
     *
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }
}
