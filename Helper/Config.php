<?php
/**
 * Sales And Orders Feed Tool
 * Copyright © 2019 S&O LLC. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace SalesAndOrders\FeedTool\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Comment is required here
 */
class Config extends AbstractHelper
{

    const XML_OPTIONS_IFRAME_LOAD_URL = 'oauth_configs/custom_config/load_iframe';

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

    /**
     * @return string
     */
    public function getIframeLoadUrl()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_OPTIONS_IFRAME_LOAD_URL,
            ScopeInterface::SCOPE_STORE
        );
    }
}
