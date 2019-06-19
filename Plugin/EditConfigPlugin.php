<?php

namespace SalesAndOrders\FeedTool\Plugin;

use Magento\Config\Controller\Adminhtml\System\Config\Save;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class EditConfigPlugin {

    protected $beforeSaveStoreName;

    protected $storeManager;

    protected $scopeConfig;

    protected $beforeBaseUrl = null;
    protected $beforeSecureUrl = null;
    protected $beforeStoreName = null;
    protected $beforeTimeZone = null;
    protected $beforeLocale = null;
    protected $beforeBaseCurrency = null;
    protected $beforeDisplayCurrency = null;

    protected $isChanged = false;

    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function beforeExecute(Save $subject)
    {
        $storeID = $subject->getRequest()->getParam('store');
        if ($storeID) {
            $this->beforeBaseUrl = $this->scopeConfig->getValue(
                'web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);

            $this->beforeSecureUrl = $this->scopeConfig->getValue(
                'web/secure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);

            $this->beforeStoreName = $this->scopeConfig->getValue(
                'general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);

            $this->beforeTimeZone = $this->scopeConfig->getValue(
                'general/locale/timezone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $this->beforeLocale = $this->scopeConfig->getValue(
                'general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);

            $this->beforeBaseCurrency = $this->scopeConfig->getValue(
                'currency/options/base', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $this->beforeDisplayCurrency = $this->scopeConfig->getValue(
                'currency/options/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);
        }
    }

    public function afterExecute(Save $subject, $result)
    {
        $storeID = $subject->getRequest()->getParam('store');

        $baseUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);

        $secureUrl = $this->scopeConfig->getValue(
            'web/secure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);

        $storeName = $this->scopeConfig->getValue(
            'general/store_information/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);

        $timeZone = $this->scopeConfig->getValue(
            'general/locale/timezone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $locale = $this->scopeConfig->getValue(
            'general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);

        $baseCurrency = $this->scopeConfig->getValue(
            'currency/options/base', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $displayCurrency = $this->scopeConfig->getValue(
            'currency/options/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeID);

        if ($baseUrl != $this->beforeBaseUrl) {
            $this->isChanged = true;
        }
        if ($secureUrl != $this->beforeSecureUrl) {
            $this->isChanged = true;
        }
        if ($storeName != $this->beforeStoreName) {
            $this->isChanged = true;
        }
        if ($timeZone != $this->beforeTimeZone) {
            $this->isChanged = true;
        }
        if ($locale != $this->beforeLocale) {
            $this->isChanged = true;
        }
        if ($baseCurrency != $this->beforeBaseCurrency) {
            $this->isChanged = true;
        }
        if ($displayCurrency != $this->beforeDisplayCurrency) {
            $this->isChanged = true;
        }

        if ($this->isChanged) {
            // curl to account url
        }


        return $result;
    }
}