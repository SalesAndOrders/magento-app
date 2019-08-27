<?php

namespace SalesAndOrders\FeedTool\Helper\System;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Comment is required here
 */
class Config extends AbstractHelper
{

    const XML_SYSTEM_STORE_BASE_URL = 'oauth_configs/custom_config/load_iframe';
    const XML_SYSTEM_SECURE_BASE_URL = 'oauth_configs/custom_config/load_iframe';
    const XML_SYSTEM_STORE_CODE = 'oauth_configs/custom_config/load_iframe';
    const XML_SYSTEM_STORE_NAME = 'oauth_configs/custom_config/load_iframe';
    const XML_SYSTEM_TIME_ZONE = 'oauth_configs/custom_config/load_iframe';
    const XML_SYSTEM_COUNTRY = 'oauth_configs/custom_config/load_iframe';
    const XML_SYSTEM_BASE_CURRENCY = 'oauth_configs/custom_config/load_iframe';
    const XML_SYSTEM_DISPLAY_CURRENCY = 'oauth_configs/custom_config/load_iframe';

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

    public function getStoreBaseUrl()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_SYSTEM_STORE_BASE_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getSecureBaseUrl()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_SYSTEM_SECURE_BASE_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreCode()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_SYSTEM_STORE_CODE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreName()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_SYSTEM_STORE_NAME,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getTimeZone()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_SYSTEM_TIME_ZONE,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCountry()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_SYSTEM_COUNTRY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getBaseCurrency()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_SYSTEM_BASE_CURRENCY,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDisplayCurrency()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_SYSTEM_DISPLAY_CURRENCY,
            ScopeInterface::SCOPE_STORE
        );
    }
}
