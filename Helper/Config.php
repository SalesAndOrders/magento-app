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
use SalesAndOrders\FeedTool\Model\Config\Source\Mode;

/**
 * Comment is required here
 */
class Config extends AbstractHelper
{
    const XML_OPTIONS_IFRAME_LOAD_URL = 'oauth_configs/custom_config/load_iframe';
    const XML_DEPLOY_MODE = 'oauth_configs/deploy/mode';
    // develop
    const XML_DEVELOP_ENVIRONMENT_DEVELOP_LOAD_IFRAME = 'sando_develop/environment_develop/load_iframe';
    const XML_DEVELOP_ENVIRONMENT_DEVELOP_INTEGRATION_EMAIL = 'sando_develop/environment_develop/integration_email';
    const XML_DEVELOP_ENVIRONMENT_DEVELOP_INTEGRATION_ENDPOINT_URL = 'sando_develop/environment_develop/integration_endpoint_url';
    const XML_DEVELOP_ENVIRONMENT_DEVELOP_INTEGRATION_IDENTITY_LINK_URL = 'sando_develop/environment_develop/integration_identity_link_url';
    // staging
    const XML_DEVELOP_ENVIRONMENT_STAGING_LOAD_IFRAME = 'sando_develop/environment_staging/load_iframe';
    const XML_DEVELOP_ENVIRONMENT_STAGING_INTEGRATION_EMAIL = 'sando_develop/environment_staging/integration_email';
    const XML_DEVELOP_ENVIRONMENT_STAGING_INTEGRATION_ENDPOINT_URL = 'sando_develop/environment_staging/integration_endpoint_url';
    const XML_DEVELOP_ENVIRONMENT_STAGING_INTEGRATION_IDENTITY_LINK_URL = 'sando_develop/environment_staging/integration_identity_link_url';
    // filters
    const XML_FILTERS_FILTER_IS_SALABLE = 'sando_filters/filter/is_salable';
    const XML_FILTERS_FILTER_IS_PRICE = 'sando_filters/filter/is_price';

    protected $deployMode = null;   // 0 - default, 1 develop 2 staging

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var Mode
     */
    protected $_objMode;
    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Mode $objMode
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Mode $objMode,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->_configValueFactory = $configValueFactory;
        $this->_objectManager = $objectManager;
        $this->_objMode = $objMode;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        parent::__construct($context);
    }
    /**
     * @param \Magento\Framework\HTTP\Header $httpHeader
     */
    public function setHttpHeader(\Magento\Framework\HTTP\Header $httpHeader)
    {
        $this->_httpHeader = $httpHeader;
    }

    /**
     * @return string
     */
    public function getIframeLoadUrl()
    {
        return ($this->getDeployMode() === 0) ? $this->getOptionsIframeLoadUrl()
                                                : ( ($this->getDeployMode() === 1 ) ? $this->getDevelopLoadIframe()
                                                                                     : $this->getStagingLoadIframe() )
            ;
    }
    /**
     * @return string
     */
    public function getOptionsIframeLoadUrl()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_OPTIONS_IFRAME_LOAD_URL,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getDevelopLoadIframe()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_DEVELOP_ENVIRONMENT_DEVELOP_LOAD_IFRAME_,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getDevelopIntegrationEmail()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_DEVELOP_ENVIRONMENT_DEVELOP_INTEGRATION_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getDevelopIntegrationEndpointURL()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_DEVELOP_ENVIRONMENT_DEVELOP_INTEGRATION_ENDPOINT_URL,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getDevelopIntegrationIdentityLinkURL()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_DEVELOP_ENVIRONMENT_DEVELOP_INTEGRATION_IDENTITY_LINK_URL,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getStagingLoadIframe()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_DEVELOP_ENVIRONMENT_STAGING_LOAD_IFRAME_,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getStagingIntegrationEmail()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_DEVELOP_ENVIRONMENT_STAGING_INTEGRATION_EMAIL,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getStagingIntegrationEndpointURL()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_DEVELOP_ENVIRONMENT_STAGING_INTEGRATION_ENDPOINT_URL,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getStagingIntegrationIdentityLinkURL()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_DEVELOP_ENVIRONMENT_STAGING_INTEGRATION_IDENTITY_LINK_URL,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getFiltersFilterIsSalable()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_FILTERS_FILTER_IS_SALABLE,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getFiltersFilterIsPrice()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_FILTERS_FILTER_IS_PRICE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return integer
     */
    public function getDeployMode(){
        if(!$this->deployMode){
            $this->deployMode = 0;  // default value
            $deployMode = (int)$this->scopeConfig->getValue(
                self::XML_DEPLOY_MODE
                , ScopeInterface::SCOPE_STORES
            );
            $this->deployMode = is_null($deployMode) ? $this->deployMode : $deployMode;
        }
        return $this->deployMode;
    }
    /**
     * @param integer $value
     */
    public function setDeployMode($value){
        try {
            $this->_configValueFactory->create()->load(
                self::XML_DEPLOY_MODE,
                'path'
                )
                ->setValue($value)
                ->setPath(self::XML_DEPLOY_MODE)
                ->save();

            //update cache for configuration
            $types = array('config');
            foreach ($types as $type) {
                $this->_cacheTypeList->cleanType($type);
            }
            foreach ($this->_cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        } catch (\Exception $e) {
            throw new \Exception(__('Can\'t save new mode.'));
        }
    }
    /**
     * @return string
     */
    public function getDeployModeTextValue(){
        return $this->_objMode->getLabelByValue($this->getDeployMode());
    }
}
