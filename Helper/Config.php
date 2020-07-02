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
    const XML_FILTERS_FILTER_ATTRIBUTE_SET = 'sando_filters/filter/attribute_set';
    const XML_FILTERS_FILTER_PRODUCT_TYPE = 'sando_filters/filter/product_type';
    const XML_FILTERS_FILTER_PRODUCT_EXCLUDE_ID = 'sando_filters/product/exclude_id';

    const XML_FILTERS_CATEGORY_CAT_ID = 'sando_filters/category/cat_id';
    /* exclude / include - default */
    const XML_FILTERS_CATEGORY_IN_EX_CLUDE_PRODUCTS = 'sando_filters/category/in_ex_clude_products';
    // custom attribute
    const XML_FILTERS_ATTRIBUTE_FIELD       = 'sando_filters/attribute/field_';
    const XML_FILTERS_ATTRIBUTE_CONDITION   = 'sando_filters/attribute/condition_';
    const XML_FILTERS_ATTRIBUTE_VALUE       = 'sando_filters/attribute/value_';
    protected $custom_poduct_attribute_count = 7;   // from 1 to 7

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
     * @return string
     */
    public function getFiltersFilterProductType()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_FILTERS_FILTER_PRODUCT_TYPE,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getFiltersFilterAttributeSet()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_FILTERS_FILTER_ATTRIBUTE_SET,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getFiltersFilterProductExcludeId()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_FILTERS_FILTER_PRODUCT_EXCLUDE_ID,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getFiltersCategoryCatId()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_FILTERS_CATEGORY_CAT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * @return string
     */
    public function getFiltersCategoryInExCludeProducts()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_FILTERS_CATEGORY_IN_EX_CLUDE_PRODUCTS,
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Retrieves custom attribute by index ar all
     * retrieves only validated :  field, value,  condition are filled
     *
     * 0 - retrieves all
     * 0 < - retrieves specific
     * @param int $index
     * @return array
     */
    public function getFiltersAttribute($index = 0)
    {
        $res = [];
        if( $index > 0 ){
            $field     =  (string)$this->scopeConfig->getValue(
                                    self::XML_FILTERS_ATTRIBUTE_FIELD.$index,
                                    ScopeInterface::SCOPE_STORE
                                );
            $condition =  (string)$this->scopeConfig->getValue(
                                    self::XML_FILTERS_ATTRIBUTE_CONDITION.$index,
                                    ScopeInterface::SCOPE_STORE
                                );
            $value    =  (string)$this->scopeConfig->getValue(
                                    self::XML_FILTERS_ATTRIBUTE_VALUE.$index,
                                    ScopeInterface::SCOPE_STORE
                                );
            if($field && $condition && $value){
                $res = [
                    'field' =>  $field,
                    'condition'=>   $condition,
                    'value' =>  $value
                ];
            }

        }else{
            for ($i = 0; $i < $this->custom_poduct_attribute_count; $i++ ){

                $field     =  (string)$this->scopeConfig->getValue(
                    self::XML_FILTERS_ATTRIBUTE_FIELD.$i,
                    ScopeInterface::SCOPE_STORE
                );
                $condition =  (string)$this->scopeConfig->getValue(
                    self::XML_FILTERS_ATTRIBUTE_CONDITION.$i,
                    ScopeInterface::SCOPE_STORE
                );
                $value    =  (string)$this->scopeConfig->getValue(
                    self::XML_FILTERS_ATTRIBUTE_VALUE.$i,
                    ScopeInterface::SCOPE_STORE
                );

                if($field && $condition && $value){
                    $res[] = [
                        'field' =>  $field,
                        'condition'=>   $condition,
                        'value' =>  $value
                    ];
                }

            }
        }
        return $res;
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
